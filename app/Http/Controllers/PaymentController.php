<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Flight;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Show Checkout Page with Midtrans Snap integration.
     */
    public function checkout(Booking $booking)
    {
        // Guard: customer can only pay for their own bookings
        if (Auth::user()->role === 'customer' && $booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        if ($booking->status !== 'pending') {
            return redirect()->route('eticket.show', $booking->booking_code)
                ->with('success', 'This booking is already ' . $booking->status);
        }

        $midtransConfig = $this->getMidtransConfig();
        $serverKey = $midtransConfig['server_key'];
        $clientKey = $midtransConfig['client_key'];
        $snapUrl = $midtransConfig['snap_url'];
        $orderId = $this->buildOrderId($booking);

        $snapToken = null;
        $errorMsg = null;

        if ($this->hasPlaceholderMidtransKeys($serverKey, $clientKey)) {
            $errorMsg = 'Please use real Sandbox credentials from Midtrans Dashboard → Settings → Access Keys. The current keys are placeholders and will cause unauthorized transaction errors.';

            return view('bookings.checkout', compact('booking', 'snapToken', 'clientKey', 'errorMsg', 'orderId'));
        }

        // Try requesting token from Midtrans Sandbox
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])->post($snapUrl, [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) round($booking->total_price),
                ],
                'credit_card' => [
                    'secure' => true,
                ],
                'customer_details' => [
                    'first_name' => $booking->user->name,
                    'email' => $booking->user->email,
                ],
                'item_details' => [
                    [
                        'id' => $booking->flight->flight_number,
                        'price' => (int) round($booking->total_price / max(1, $booking->passengers->count())),
                        'quantity' => max(1, $booking->passengers->count()),
                        'name' => 'Flight Ticket ' . $booking->flight->flight_number,
                    ]
                ]
            ]);

            if ($response->successful()) {
                $snapToken = $response->json('token');
            } else {
                $errorMsg = 'Midtrans API Error: ' . $response->json('error_messages.0', 'Failed to connect');
            }
        } catch (\Exception $e) {
            $errorMsg = 'Could not connect to Midtrans Payment Gateway: ' . $e->getMessage();
        }

        return view('bookings.checkout', compact('booking', 'snapToken', 'clientKey', 'errorMsg', 'orderId'));
    }

    /**
     * Midtrans Webhook Listener.
     */
    public function webhook(Request $request)
    {
        $midtransConfig = $this->getMidtransConfig();
        $serverKey = $midtransConfig['server_key'];

        $orderIdWithTime = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $signatureKey = $request->input('signature_key');

        // Extract original booking code from order_id (Format: BOOKINGCODE-TIMESTAMP)
        $parts = explode('-', $orderIdWithTime);
        $bookingCode = $parts[0];

        // Validate Midtrans signature key
        $localSignature = hash('sha512', $orderIdWithTime . $statusCode . $grossAmount . $serverKey);
        
        if ($localSignature !== $signatureKey) {
            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        $booking = Booking::where('booking_code', $bookingCode)->first();
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $transactionStatus = $request->input('transaction_status');
        $paymentType = $request->input('payment_type');
        $transactionId = $request->input('transaction_id');

        $this->processPaymentUpdate($booking, $transactionStatus, $paymentType, $transactionId, $grossAmount);

        return response()->json(['message' => 'Webhook processed successfully']);
    }

    /**
     * Manual Payment Checker & Local Mock Payment Simulator.
     */
    public function checkPaymentStatus(Booking $booking, Request $request)
    {
        // Guard: customer can only check their own bookings
        if (Auth::user()->role === 'customer' && $booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        // Handle offline/local payment simulation request
        if ($request->has('simulate') && $request->input('simulate') === 'success') {
            $this->processPaymentUpdate(
                $booking, 
                'settlement', 
                'Local Simulator', 
                'SIM-' . strtoupper(Str::random(10)), 
                $booking->total_price
            );
            return redirect()->route('bookings.history')
                ->with('success', 'Payment simulated successfully! Waiting for staff confirmation.');
        }

        $midtransConfig = $this->getMidtransConfig();
        $serverKey = $midtransConfig['server_key'];
        $statusUrl = rtrim($midtransConfig['status_url'], '/');
        $orderId = $request->input('order_id', $booking->booking_code);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])->get("{$statusUrl}/{$orderId}/status");

            if ($response->successful()) {
                $status = $response->json('transaction_status');
                $type = $response->json('payment_type');
                $txId = $response->json('transaction_id');
                $amount = $response->json('gross_amount');
                $this->processPaymentUpdate($booking, $status, $type, $txId, $amount);

                if ($booking->fresh()->status === 'waiting') {
                    return redirect()->route('bookings.history')
                        ->with('success', 'Payment completed successfully! Waiting for staff confirmation.');
                }
            }
        } catch (\Exception $e) {
            // Ignore connection errors on status checking, fallback to page warning
        }

        return back()->with('error', 'Payment status is still pending. If you just paid, please wait a few seconds and try checking again, or use the Simulator below.');
    }

    /**
     * Core payment status updating business logic.
     */
    private function getMidtransConfig(): array
    {
        return [
            'server_key' => trim((string) config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY', ''))),
            'client_key' => trim((string) config('services.midtrans.client_key', env('MIDTRANS_CLIENT_KEY', ''))),
            'snap_url' => config('services.midtrans.snap_url', env('MIDTRANS_IS_SANDBOX', true) ? 'https://app.sandbox.midtrans.com/snap/v1/transactions' : 'https://app.midtrans.com/snap/v1/transactions'),
            'status_url' => config('services.midtrans.status_url', env('MIDTRANS_IS_SANDBOX', true) ? 'https://api.sandbox.midtrans.com/v2' : 'https://api.midtrans.com/v2'),
        ];
    }

    private function hasPlaceholderMidtransKeys(string $serverKey, string $clientKey): bool
    {
        return $serverKey === '' || $clientKey === '' || str_contains(strtolower($serverKey), 'mockkey') || str_contains(strtolower($clientKey), 'mockkey');
    }

    private function buildOrderId(Booking $booking): string
    {
        return $booking->booking_code . '-' . time();
    }

    private function processPaymentUpdate(Booking $booking, $transactionStatus, $paymentType, $transactionId, $amount)
    {
        // Prevent reprocessing already confirmed/cancelled bookings
        if ($booking->status !== 'pending') {
            return;
        }

        if (in_array($transactionStatus, ['settlement', 'capture'])) {
            // Payment Success
            $booking->status = 'waiting';
            $booking->save();

            Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'payment_method' => $paymentType,
                    'payment_status' => 'paid',
                    'transaction_code' => $transactionId,
                    'amount' => $amount,
                ]
            );
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            // Payment Failed / Cancelled
            $booking->status = 'cancelled';
            $booking->save();

            Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'payment_method' => $paymentType,
                    'payment_status' => 'failed',
                    'transaction_code' => $transactionId,
                    'amount' => $amount,
                ]
            );

            // Return seats back to the flight
            $flight = $booking->flight;
            $flight->available_seats += $booking->passengers->count();
            $flight->save();
        }
    }
}
