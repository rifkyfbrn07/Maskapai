@extends('layouts.app')

@section('title', 'Add Airplane')

@section('content')
<div class="max-w-xl mx-auto py-4">
    <div class="mb-8 pb-6 border-b border-slate-900">
        <a href="{{ route('airplanes.index') }}" class="inline-flex items-center text-sm font-medium text-cyan-400 hover:text-cyan-300 transition mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to list
        </a>
        <h1 class="text-3xl font-extrabold text-white font-display">Add New Aircraft</h1>
        <p class="text-sm text-slate-400 mt-1">Register a new airplane asset and map its seating layout</p>
    </div>

    <!-- Alert explaining auto seat layout creation -->
    <div class="mb-6 flex items-start p-4 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 rounded-2xl text-xs">
        <i data-lucide="info" class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5 animate-pulse"></i>
        <div>
            <strong class="font-bold block mb-1">Automated Seating Setup:</strong>
            When you create this airplane, the system will automatically generate all matching seats in rows A-F. 
            The first 15% of seat rows will be designated as **Business Class** and the rest as **Economy Class** to populate the layout maps.
        </div>
    </div>

    <div class="glass-card rounded-2xl p-6 border border-slate-900 relative overflow-hidden">
        <form action="{{ route('airplanes.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Airline Select -->
            <div>
                <label for="airline_id" class="block text-sm font-semibold text-slate-300 mb-2">Operating Airline</label>
                <div class="relative">
                    <select name="airline_id" id="airline_id" required autofocus
                        class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition appearance-none">
                        <option value="">Select Airline...</option>
                        @foreach($airlines as $airline)
                            <option value="{{ $airline->id }}" {{ old('airline_id') == $airline->id ? 'selected' : '' }}>{{ $airline->name }} ({{ $airline->code }})</option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i data-lucide="chevron-down" class="h-4 w-4 text-slate-500"></i>
                    </span>
                </div>
                @error('airline_id')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Aircraft Model -->
            <div>
                <label for="model" class="block text-sm font-semibold text-slate-300 mb-2">Aircraft Model</label>
                <input type="text" name="model" id="model" value="{{ old('model') }}" required
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                    placeholder="Boeing 737-800">
                @error('model')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Capacity -->
            <div>
                <label for="capacity" class="block text-sm font-semibold text-slate-300 mb-2">Capacity (Total Seats)</label>
                <input type="number" name="capacity" id="capacity" value="{{ old('capacity', 180) }}" required min="10" max="500"
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                    placeholder="e.g. 180">
                @error('capacity')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full mt-4 py-3 px-4 bg-cyan-500 hover:bg-cyan-400 text-sm font-bold text-white rounded-xl shadow-lg shadow-cyan-950/20 glow-cyan transition">
                Create Aircraft Fleet
            </button>
        </form>
    </div>
</div>
@endsection
