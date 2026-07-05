<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flight;
use App\Models\Airplane;
use App\Models\Airport;

class FlightCrudController extends Controller
{
    public function index()
    {
        $flights = Flight::with(['airplane.airline', 'departureAirport', 'arrivalAirport'])
            ->orderBy('departure_time', 'desc')
            ->get();
        return view('admin.flights.index', compact('flights'));
    }

    public function create()
    {
        $airplanes = Airplane::with('airline')->get();
        $airports = Airport::orderBy('city')->get();
        return view('admin.flights.create', compact('airplanes', 'airports'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'airplane_id' => 'required|exists:airplanes,id',
            'flight_number' => 'required|string|max:50',
            'departure_airport_id' => 'required|exists:airports,id',
            'arrival_airport_id' => 'required|exists:airports,id|different:departure_airport_id',
            'departure_time' => 'required|date|after:today',
            'arrival_time' => 'required|date|after:departure_time',
            'price' => 'required|numeric|min:0',
        ]);

        $airplane = Airplane::findOrFail($request->airplane_id);

        Flight::create([
            'airplane_id' => $request->airplane_id,
            'flight_number' => strtoupper($request->flight_number),
            'departure_airport_id' => $request->departure_airport_id,
            'arrival_airport_id' => $request->arrival_airport_id,
            'departure_time' => $request->departure_time,
            'arrival_time' => $request->arrival_time,
            'price' => $request->price,
            'available_seats' => $airplane->capacity, // Default available seats to total airplane capacity
        ]);

        return redirect()->route('flights.index')->with('success', 'Flight scheduled successfully.');
    }

    public function edit(Flight $flight)
    {
        $airplanes = Airplane::with('airline')->get();
        $airports = Airport::orderBy('city')->get();
        return view('admin.flights.edit', compact('flight', 'airplanes', 'airports'));
    }

    public function update(Request $request, Flight $flight)
    {
        $request->validate([
            'airplane_id' => 'required|exists:airplanes,id',
            'flight_number' => 'required|string|max:50',
            'departure_airport_id' => 'required|exists:airports,id',
            'arrival_airport_id' => 'required|exists:airports,id|different:departure_airport_id',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'price' => 'required|numeric|min:0',
        ]);

        // Guard: check if capacity change is valid
        $airplane = Airplane::findOrFail($request->airplane_id);
        $difference = $airplane->capacity - $flight->airplane->capacity;
        
        $newAvailableSeats = $flight->available_seats + $difference;
        if ($newAvailableSeats < 0) {
            return back()->with('error', 'Cannot change to this airplane. It has a lower capacity than the seats already booked.');
        }

        $flight->update([
            'airplane_id' => $request->airplane_id,
            'flight_number' => strtoupper($request->flight_number),
            'departure_airport_id' => $request->departure_airport_id,
            'arrival_airport_id' => $request->arrival_airport_id,
            'departure_time' => $request->departure_time,
            'arrival_time' => $request->arrival_time,
            'price' => $request->price,
            'available_seats' => $newAvailableSeats,
        ]);

        return redirect()->route('flights.index')->with('success', 'Flight details updated successfully.');
    }

    public function destroy(Flight $flight)
    {
        if ($flight->bookings()->where('status', '!=', 'cancelled')->exists()) {
            return back()->with('error', 'Cannot cancel flight. Active bookings are registered for this flight.');
        }

        $flight->delete();
        return redirect()->route('flights.index')->with('success', 'Flight cancelled and deleted successfully.');
    }
}
