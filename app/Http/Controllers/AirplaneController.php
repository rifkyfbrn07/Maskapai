<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Airplane;
use App\Models\Airline;
use App\Models\Seat;

class AirplaneController extends Controller
{
    public function index()
    {
        $airplanes = Airplane::with('airline')->orderBy('id', 'desc')->get();
        return view('admin.airplanes.index', compact('airplanes'));
    }

    public function create()
    {
        $airlines = Airline::orderBy('name')->get();
        return view('admin.airplanes.create', compact('airlines'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'airline_id' => 'required|exists:airlines,id',
            'model' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:30',
            'capacity' => 'required|integer|min:10|max:500',
        ]);

        $airplane = Airplane::create([
            'airline_id' => $request->airline_id,
            'model' => $request->model,
            'registration_number' => $request->input('registration_number'),
            'capacity' => $request->capacity,
        ]);

        // Auto-generate seats mapping based on capacity
        // Assume 6 seats per row: A, B, C, D, E, F
        $seatLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
        $capacity = $request->capacity;
        $totalRows = ceil($capacity / 6);
        
        // Define Business Class to be 15% of total rows (minimum 2 rows)
        $businessRows = max(2, ceil($totalRows * 0.15));

        $seats = [];
        $seatCount = 0;
        
        for ($row = 1; $row <= $totalRows; $row++) {
            $class = ($row <= $businessRows) ? 'business' : 'economy';
            foreach ($seatLetters as $letter) {
                if ($seatCount >= $capacity) {
                    break 2; // stop outer loops if capacity reached
                }
                
                $seats[] = [
                    'airplane_id' => $airplane->id,
                    'seat_number' => $row . $letter,
                    'class' => $class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $seatCount++;
            }
        }

        Seat::insert($seats);

        return redirect()->route('airplanes.index')->with('success', "Airplane and {$seatCount} seats created and mapped successfully.");
    }

    public function edit(Airplane $airplane)
    {
        $airlines = Airline::orderBy('name')->get();
        return view('admin.airplanes.edit', compact('airplane', 'airlines'));
    }

    public function update(Request $request, Airplane $airplane)
    {
        $request->validate([
            'airline_id' => 'required|exists:airlines,id',
            'model' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:30',
            // Capacity editing is restricted because it affects seats structure
        ]);

        $data = [
            'airline_id' => $request->airline_id,
            'model' => $request->model,
        ];

        if ($request->filled('registration_number')) {
            $data['registration_number'] = $request->registration_number;
        }

        $airplane->update($data);

        return redirect()->route('airplanes.index')->with('success', 'Airplane details updated successfully.');
    }

    public function destroy(Airplane $airplane)
    {
        if ($airplane->flights()->exists()) {
            return back()->with('error', 'Cannot delete airplane. It is currently scheduled for active flights.');
        }

        $airplane->delete(); // cascading delete in DB will clear seats automatically
        return redirect()->route('airplanes.index')->with('success', 'Airplane and its associated seats deleted successfully.');
    }
}
