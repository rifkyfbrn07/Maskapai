@extends('layouts.app')

@section('title', 'Edit Scheduled Flight')

@section('content')
<div class="max-w-xl mx-auto py-4">
    <div class="mb-8 pb-6 border-b border-slate-900">
        <a href="{{ route('flights.index') }}" class="inline-flex items-center text-sm font-medium text-cyan-400 hover:text-cyan-300 transition mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to list
        </a>
        <h1 class="text-3xl font-extrabold text-white font-display">Edit Scheduled Flight</h1>
        <p class="text-sm text-slate-400 mt-1">Modify timing, aircraft, or pricing rules for flight {{ $flight->flight_number }}</p>
    </div>

    <div class="glass-card rounded-2xl p-6 border border-slate-900 relative overflow-hidden">
        <form action="{{ route('flights.update', $flight->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Flight Number -->
            <div>
                <label for="flight_number" class="block text-sm font-semibold text-slate-300 mb-2">Flight Number</label>
                <input type="text" name="flight_number" id="flight_number" value="{{ old('flight_number', $flight->flight_number) }}" required autofocus
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm font-mono tracking-widest focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                    placeholder="GA-220">
                @error('flight_number')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Airplane Select -->
            <div>
                <label for="airplane_id" class="block text-sm font-semibold text-slate-300 mb-2">Assigned Aircraft</label>
                <div class="relative">
                    <select name="airplane_id" id="airplane_id" required
                        class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition appearance-none">
                        @foreach($airplanes as $airplane)
                            <option value="{{ $airplane->id }}" {{ old('airplane_id', $flight->airplane_id) == $airplane->id ? 'selected' : '' }}>{{ $airplane->airline->name }} - {{ $airplane->model }} (Cap: {{ $airplane->capacity }} seats)</option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i data-lucide="chevron-down" class="h-4 w-4 text-slate-500"></i>
                    </span>
                </div>
                @error('airplane_id')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Route Info: Origin and Destination -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Departure Airport -->
                <div>
                    <label for="departure_airport_id" class="block text-sm font-semibold text-slate-300 mb-2">Origin Hub</label>
                    <div class="relative">
                        <select name="departure_airport_id" id="departure_airport_id" required
                            class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition appearance-none">
                            @foreach($airports as $airport)
                                <option value="{{ $airport->id }}" {{ old('departure_airport_id', $flight->departure_airport_id) == $airport->id ? 'selected' : '' }}>{{ $airport->city }} ({{ $airport->iata_code }})</option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="h-4 w-4 text-slate-500"></i>
                        </span>
                    </div>
                    @error('departure_airport_id')
                        <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Arrival Airport -->
                <div>
                    <label for="arrival_airport_id" class="block text-sm font-semibold text-slate-300 mb-2">Destination Hub</label>
                    <div class="relative">
                        <select name="arrival_airport_id" id="arrival_airport_id" required
                            class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition appearance-none">
                            @foreach($airports as $airport)
                                <option value="{{ $airport->id }}" {{ old('arrival_airport_id', $flight->arrival_airport_id) == $airport->id ? 'selected' : '' }}>{{ $airport->city }} ({{ $airport->iata_code }})</option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="h-4 w-4 text-slate-500"></i>
                        </span>
                    </div>
                    @error('arrival_airport_id')
                        <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Timings: Departure and Arrival -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Departure Time -->
                <div>
                    <label for="departure_time" class="block text-sm font-semibold text-slate-300 mb-2">Departure Time</label>
                    <input type="datetime-local" name="departure_time" id="departure_time" value="{{ old('departure_time', date('Y-m-d\TH:i', strtotime($flight->departure_time))) }}" required
                        class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition">
                    @error('departure_time')
                        <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Arrival Time -->
                <div>
                    <label for="arrival_time" class="block text-sm font-semibold text-slate-300 mb-2">Arrival Time</label>
                    <input type="datetime-local" name="arrival_time" id="arrival_time" value="{{ old('arrival_time', date('Y-m-d\TH:i', strtotime($flight->arrival_time))) }}" required
                        class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition">
                    @error('arrival_time')
                        <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Price -->
            <div>
                <label for="price" class="block text-sm font-semibold text-slate-300 mb-2">Base Ticket Price (IDR)</label>
                <input type="number" name="price" id="price" value="{{ old('price', (int)$flight->price) }}" required min="0"
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                    placeholder="e.g. 1200000">
                @error('price')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full mt-4 py-3 px-4 bg-cyan-500 hover:bg-cyan-400 text-sm font-bold text-white rounded-xl shadow-lg shadow-cyan-950/20 glow-cyan transition">
                Update Scheduled Flight
            </button>
        </form>
    </div>
</div>
@endsection
