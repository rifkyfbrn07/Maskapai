<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\Booking;

class StaffController extends Controller
{
    /**
     * Display Staff Dashboard (Flights tracking list and waiting bookings).
     */
    public function index()
    {
        $flights = Flight::with(['airplane.airline', 'departureAirport', 'arrivalAirport'])
            ->where('arrival_time', '>=', now())
            ->withCount(['bookings as active_bookings_count' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }])
            ->orderBy('departure_time', 'desc')
            ->get();

        $waitingBookings = Booking::where('status', 'waiting')
            ->with(['user', 'flight.departureAirport', 'flight.arrivalAirport', 'flight.airplane.airline'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('staff.dashboard', compact('flights', 'waitingBookings'));
    }

    /**
     * Show Passenger Manifest for a specific flight.
     */
    public function manifest(Flight $flight)
    {
        $flight->load(['airplane.airline', 'departureAirport', 'arrivalAirport']);

        $passengers = Passenger::whereHas('booking', function ($q) use ($flight) {
            $q->where('flight_id', $flight->id)
              ->where('status', '!=', 'cancelled');
        })
        ->with('booking.user')
        ->orderBy('seat_number')
        ->get();

        return view('staff.manifest', compact('flight', 'passengers'));
    }

    /**
     * Confirm a waiting booking.
     */
    public function confirmBooking(Booking $booking)
    {
        if ($booking->status !== 'waiting') {
            return redirect()->route('staff.dashboard')->with('error', 'Booking is not waiting for confirmation.');
        }

        $booking->status = 'confirmed';
        $booking->save();

        return redirect()->route('staff.dashboard')->with('success', 'Booking PNR ' . $booking->booking_code . ' confirmed successfully!');
    }

    /**
     * Cancel/Reject a waiting booking.
     */
    public function cancelBooking(Booking $booking)
    {
        if ($booking->status !== 'waiting') {
            return redirect()->route('staff.dashboard')->with('error', 'Booking cannot be cancelled.');
        }

        $booking->status = 'cancelled';
        $booking->save();

        // Return seats back to the flight
        $flight = $booking->flight;
        $flight->available_seats += $booking->passengers->count();
        $flight->save();

        return redirect()->route('staff.dashboard')->with('success', 'Booking PNR ' . $booking->booking_code . ' cancelled.');
    }
}
