<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Airline;

class AirlineController extends Controller
{
    public function index()
    {
        $airlines = Airline::orderBy('name')->get();
        return view('admin.airlines.index', compact('airlines'));
    }

    public function create()
    {
        return view('admin.airlines.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:airlines,code',
            'logo' => 'nullable|url|max:2048',
        ]);

        Airline::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'logo' => $request->logo,
            'photos' => json_encode([]),
        ]);

        return redirect()->route('airlines.index')->with('success', 'Airline created successfully.');
    }

    public function edit(Airline $airline)
    {
        return view('admin.airlines.edit', compact('airline'));
    }

    public function update(Request $request, Airline $airline)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:airlines,code,' . $airline->id,
            'logo' => 'nullable|url|max:2048',
        ]);

        $airline->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'logo' => $request->logo,
        ]);

        return redirect()->route('airlines.index')->with('success', 'Airline updated successfully.');
    }

    public function destroy(Airline $airline)
    {
        if ($airline->airplanes()->exists()) {
            return back()->with('error', 'Cannot delete airline. It still has registered airplanes.');
        }

        $airline->delete();
        return redirect()->route('airlines.index')->with('success', 'Airline deleted successfully.');
    }
}
