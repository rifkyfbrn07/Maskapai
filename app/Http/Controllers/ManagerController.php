<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\Airline;
use App\Models\Airport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerController extends Controller
{
    /**
     * Display Manager Dashboard.
     */
    public function index()
    {
        // 1. Core KPIs
        $totalRevenue = Booking::where('status', 'confirmed')->sum('total_price');
        $totalBookings = Booking::where('status', 'confirmed')->count();
        $totalPassengers = Passenger::whereHas('booking', function ($q) {
            $q->where('status', 'confirmed');
        })->count();

        // 2. Revenue per Airline
        $revenuePerAirline = Airline::select('airlines.name', DB::raw('SUM(bookings.total_price) as total_sales'))
            ->join('airplanes', 'airlines.id', '=', 'airplanes.airline_id')
            ->join('flights', 'airplanes.id', '=', 'flights.airplane_id')
            ->join('bookings', 'flights.id', '=', 'bookings.flight_id')
            ->where('bookings.status', 'confirmed')
            ->groupBy('airlines.id', 'airlines.name')
            ->get();

        // 3. Top Routes (Origin City -> Destination City)
        $topRoutes = Flight::select(
                'dep.city as departure_city', 'dep.iata_code as departure_iata',
                'arr.city as arrival_city', 'arr.iata_code as arrival_iata',
                DB::raw('COUNT(bookings.id) as booking_count')
            )
            ->join('airports as dep', 'flights.departure_airport_id', '=', 'dep.id')
            ->join('airports as arr', 'flights.arrival_airport_id', '=', 'arr.id')
            ->join('bookings', 'flights.id', '=', 'bookings.flight_id')
            ->where('bookings.status', 'confirmed')
            ->groupBy('flights.departure_airport_id', 'flights.arrival_airport_id', 'dep.city', 'dep.iata_code', 'arr.city', 'arr.iata_code')
            ->orderBy('booking_count', 'desc')
            ->limit(5)
            ->get();

        // 4. Seating Class Occupancy Ratio
        $classOccupancy = Passenger::select('seats.class', DB::raw('COUNT(passengers.id) as passenger_count'))
            ->join('bookings', 'passengers.booking_id', '=', 'bookings.id')
            ->join('flights', 'bookings.flight_id', '=', 'flights.id')
            // Join seats to find the class of the seat number of the airplane
            ->join('seats', function($join) {
                $join->on('flights.airplane_id', '=', 'seats.airplane_id')
                     ->on('passengers.seat_number', '=', 'seats.seat_number');
            })
            ->where('bookings.status', 'confirmed')
            ->groupBy('seats.class')
            ->get();

        // 5. Weekly Sales Trend (Last 7 Days)
        $weeklySales = Booking::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as sales'))
            ->where('status', 'confirmed')
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'day' => Carbon::parse($item->date)->format('D, M d'),
                    'sales' => (float)$item->sales,
                ];
            });

        // 6. Recent Confirmed Bookings list
        $recentBookings = Booking::where('status', 'confirmed')
            ->with(['user', 'flight.departureAirport', 'flight.arrivalAirport', 'flight.airplane.airline'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('manager.dashboard', compact(
            'totalRevenue', 'totalBookings', 'totalPassengers',
            'revenuePerAirline', 'topRoutes', 'classOccupancy', 'weeklySales', 'recentBookings'
        ));
    }

    /**
     * Display Manager Sales Report with filters.
     */
    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $airlineId = $request->input('airline_id');

        $query = Booking::where('status', 'confirmed')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->with(['user', 'flight.departureAirport', 'flight.arrivalAirport', 'flight.airplane.airline', 'passengers']);

        if ($airlineId) {
            $query->whereHas('flight.airplane', function ($q) use ($airlineId) {
                $q->where('airline_id', $airlineId);
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();
        $totalRevenue = $bookings->sum('total_price');
        $airlines = Airline::all();

        return view('manager.report', compact('bookings', 'totalRevenue', 'startDate', 'endDate', 'airlineId', 'airlines'));
    }
}
