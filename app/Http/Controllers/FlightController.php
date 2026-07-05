<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Airport;
use App\Models\Flight;
use App\Models\Airline;
use Carbon\Carbon;

class FlightController extends Controller
{
    /**
     * Display landing page with search form.
     */
    public function index()
    {
        $airports = Airport::orderBy('city')->get();
        return view('flights.index', compact('airports'));
    }

    /**
     * Search flights based on criteria and apply filters.
     */
    public function search(Request $request)
    {
        $request->validate([
            'from' => 'required|exists:airports,id',
            'to' => 'required|exists:airports,id|different:from',
            'date' => 'required|date|after_or_equal:today',
            'class' => 'required|string|in:economy,business,first',
        ]);

        $fromId = $request->input('from');
        $toId = $request->input('to');
        $date = $request->input('date');
        $seatClass = $request->input('class');

        // Parse search date range (00:00:00 to 23:59:59)
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        // Base Query
        $query = Flight::with(['airplane.airline', 'departureAirport', 'arrivalAirport'])
            ->where('departure_airport_id', $fromId)
            ->where('arrival_airport_id', $toId)
            ->whereBetween('departure_time', [$startOfDay, $endOfDay]);

        // Apply filters
        if ($request->filled('airline')) {
            $query->whereHas('airplane', function ($q) use ($request) {
                $q->where('airline_id', $request->input('airline'));
            });
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->input('price_min'));
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->input('price_max'));
        }

        if ($request->filled('time_period')) {
            $period = $request->input('time_period');
            if ($period === 'morning') {
                $query->whereTime('departure_time', '>=', '06:00:00')
                      ->whereTime('departure_time', '<', '12:00:00');
            } elseif ($period === 'afternoon') {
                $query->whereTime('departure_time', '>=', '12:00:00')
                      ->whereTime('departure_time', '<', '18:00:00');
            } elseif ($period === 'evening') {
                $query->whereTime('departure_time', '>=', '18:00:00')
                      ->whereTime('departure_time', '<=', '23:59:59');
            }
        }

        // Apply sorting
        $sort = $request->input('sort', 'price_asc');
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'time_asc') {
            $query->orderBy('departure_time', 'asc');
        } elseif ($sort === 'time_desc') {
            $query->orderBy('departure_time', 'desc');
        }

        $flights = $query->get();

        // Calculate custom prices based on seat class
        // Business: +50%, First: +100%
        $priceModifier = 1.0;
        if ($seatClass === 'business') {
            $priceModifier = 1.5;
        } elseif ($seatClass === 'first') {
            $priceModifier = 2.0;
        }

        foreach ($flights as $flight) {
            $flight->display_price = $flight->price * $priceModifier;
        }

        // Load airports and airlines for filters
        $airports = Airport::orderBy('city')->get();
        $airlines = Airline::orderBy('name')->get();
        
        $fromAirport = Airport::find($fromId);
        $toAirport = Airport::find($toId);

        return view('flights.results', compact(
            'flights', 'airports', 'airlines', 
            'fromAirport', 'toAirport', 'date', 'seatClass', 'priceModifier'
        ));
    }

    /**
     * Autocomplete search for airports.
     */
    public function searchAirports(Request $request)
    {
        $search = $request->input('q');
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $airports = Airport::where('city', 'like', "%{$search}%")
            ->orWhere('iata_code', 'like', "%{$search}%")
            ->orWhere('name', 'like', "%{$search}%")
            ->limit(10)
            ->get();

        return response()->json($airports);
    }
}
