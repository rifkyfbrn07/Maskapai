<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Airport;
use App\Models\Airline;
use App\Models\Airplane;
use App\Models\Flight;
use App\Models\Booking;

class AdminController extends Controller
{
    /**
     * Display Admin Dashboard.
     */
    public function index()
    {
        $airportCount = Airport::count();
        $airlineCount = Airline::count();
        $airplaneCount = Airplane::count();
        $flightCount = Flight::count();
        $bookingCount = Booking::count();

        // Get recent bookings
        $recentBookings = Booking::with(['user', 'flight.departureAirport', 'flight.arrivalAirport'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'airportCount', 'airlineCount', 'airplaneCount', 'flightCount', 'bookingCount', 'recentBookings'
        ));
    }
}
