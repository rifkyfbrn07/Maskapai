<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Airport;

class AirportController extends Controller
{
    public function index()
    {
        $airports = Airport::orderBy('city')->get();
        return view('admin.airports.index', compact('airports'));
    }

    public function create()
    {
        return view('admin.airports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'iata_code' => 'required|string|size:3|unique:airports,iata_code',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        Airport::create([
            'name' => $request->name,
            'iata_code' => strtoupper($request->iata_code),
            'city' => $request->city,
            'country' => $request->country,
        ]);

        return redirect()->route('airports.index')->with('success', 'Airport created successfully.');
    }

    public function edit(Airport $airport)
    {
        return view('admin.airports.edit', compact('airport'));
    }

    public function update(Request $request, Airport $airport)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'iata_code' => 'required|string|size:3|unique:airports,iata_code,' . $airport->id,
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $airport->update([
            'name' => $request->name,
            'iata_code' => strtoupper($request->iata_code),
            'city' => $request->city,
            'country' => $request->country,
        ]);

        return redirect()->route('airports.index')->with('success', 'Airport updated successfully.');
    }

    public function destroy(Airport $airport)
    {
        // Check if airport is linked to flights
        if ($airport->departingFlights()->exists() || $airport->arrivingFlights()->exists()) {
            return back()->with('error', 'Cannot delete airport. It is currently linked to active flights.');
        }

        $airport->delete();
        return redirect()->route('airports.index')->with('success', 'Airport deleted successfully.');
    }
}
