<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flight;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Show booking form with passenger manifest and seat picker.
     */
    public function create(Flight $flight, Request $request)
    {
        $seatClass = $request->input('class', 'economy');
        $flight->load(['airplane', 'departureAirport', 'arrivalAirport']);

        // Fetch all seats configured for this airplane
        $allSeats = Seat::where('airplane_id', $flight->airplane_id)
            ->where('class', $seatClass)
            ->orderByRaw('CAST(REGEXP_REPLACE(seat_number, "[^0-9]", "") AS UNSIGNED) ASC')
            ->orderByRaw('REGEXP_REPLACE(seat_number, "[0-9]", "") ASC')
            ->get();

        // If REGEXP_REPLACE is not supported on some MySQL versions, fallback to standard sort
        if ($allSeats->isEmpty()) {
            $allSeats = Seat::where('airplane_id', $flight->airplane_id)
                ->where('class', $seatClass)
                ->get()
                ->sortBy(function($seat) {
                    preg_match('/(\d+)([A-Z])/', $seat->seat_number, $matches);
                    return isset($matches[1]) ? (int)$matches[1] * 100 + ord($matches[2]) : 0;
                });
        }

        // Fetch occupied seats for this flight (bookings NOT cancelled)
        $occupiedSeats = Passenger::whereHas('booking', function ($q) use ($flight) {
            $q->where('flight_id', $flight->id)
              ->where('status', '!=', 'cancelled');
        })->pluck('seat_number')->toArray();

        // Calculate price modifier
        $priceModifier = 1.0;
        if ($seatClass === 'business') {
            $priceModifier = 1.5;
        } elseif ($seatClass === 'first') {
            $priceModifier = 2.0;
        }
        $pricePerPassenger = $flight->price * $priceModifier;

        return view('bookings.create', compact(
            'flight', 'allSeats', 'occupiedSeats', 'seatClass', 'pricePerPassenger'
        ));
    }

    /**
     * Store booking inside database transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'flight_id' => 'required|exists:flights,id',
            'class' => 'required|string|in:economy,business,first',
            'passengers' => 'required|array|min:1',
            'passengers.*.name' => 'required|string|max:255',
            'passengers.*.gender' => 'required|string|in:male,female',
            'passengers.*.birth_date' => 'required|date|before:today',
            'passengers.*.passport_number' => 'required|string|max:50',
            'passengers.*.seat_number' => 'required|string',
        ]);

        $flightId = $request->input('flight_id');
        $seatClass = $request->input('class');
        $passengersData = $request->input('passengers');
        $seatNumbers = collect($passengersData)->pluck('seat_number')->toArray();

        // 0% double-booking concurrency lock
        try {
            $booking = DB::transaction(function () use ($flightId, $seatClass, $passengersData, $seatNumbers) {
                // 1. Lock flight row for update to prevent race conditions on seats capacity
                $flight = Flight::where('id', $flightId)->lockForUpdate()->first();

                if ($flight->available_seats < count($passengersData)) {
                    throw new \Exception('Not enough available seats on this flight.');
                }

                // 2. Double check if any selected seat is already occupied
                $alreadyOccupied = Passenger::whereHas('booking', function ($q) use ($flightId) {
                    $q->where('flight_id', $flightId)
                      ->where('status', '!=', 'cancelled');
                })->whereIn('seat_number', $seatNumbers)->exists();

                if ($alreadyOccupied) {
                    throw new \Exception('One or more of the selected seats are already booked. Please refresh and try again.');
                }

                // 3. Calculate Pricing
                $priceModifier = 1.0;
                if ($seatClass === 'business') {
                    $priceModifier = 1.5;
                } elseif ($seatClass === 'first') {
                    $priceModifier = 2.0;
                }
                $totalPrice = ($flight->price * $priceModifier) * count($passengersData);

                // 4. Generate unique booking code (PNR)
                do {
                    $bookingCode = strtoupper(Str::random(6));
                } while (Booking::where('booking_code', $bookingCode)->exists());

                // 5. Create Booking
                $booking = Booking::create([
                    'user_id' => Auth::id(),
                    'flight_id' => $flightId,
                    'booking_code' => $bookingCode,
                    'total_passengers' => count($passengersData),
                    'status' => 'pending',
                    'total_price' => $totalPrice,
                ]);

                // 6. Create Passenger Manifest entries
                foreach ($passengersData as $pData) {
                    Passenger::create([
                        'booking_id' => $booking->id,
                        'full_name' => $pData['name'],
                        'gender' => $pData['gender'],
                        'birth_date' => $pData['birth_date'],
                        'passport_number' => $pData['passport_number'],
                        'seat_number' => $pData['seat_number'],
                    ]);
                }

                // 7. Deduct available seats
                $flight->available_seats -= count($passengersData);
                $flight->save();

                return $booking;
            });

            return redirect()->route('bookings.checkout', $booking->id);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Show booking history for current customer.
     */
    public function history()
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->with(['flight.departureAirport', 'flight.arrivalAirport', 'flight.airplane.airline'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('bookings.history', compact('bookings'));
    }

    /**
     * Show boarding pass / E-ticket.
     */
    public function eticket($booking_code)
    {
        $booking = Booking::where('booking_code', $booking_code)
            ->with(['flight.departureAirport', 'flight.arrivalAirport', 'flight.airplane.airline', 'passengers', 'user'])
            ->firstOrFail();

        // Guard: customer can only view their own ticket, staff and admin can view any
        if (Auth::user()->role === 'customer') {
            if ($booking->user_id !== Auth::id()) {
                abort(403, 'Unauthorized.');
            }
            if ($booking->status !== 'confirmed') {
                return redirect()->route('bookings.history')
                    ->with('error', 'Your booking has not been confirmed by our staff yet.');
            }
        }

        return view('bookings.eticket', compact('booking'));
    }
}
