@extends('layouts.app')

@section('title', 'Edit Airplane')

@section('content')
<div class="max-w-xl mx-auto py-4">
    <div class="mb-8 pb-6 border-b border-slate-900">
        <a href="{{ route('airplanes.index') }}" class="inline-flex items-center text-sm font-medium text-cyan-400 hover:text-cyan-300 transition mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to list
        </a>
        <h1 class="text-3xl font-extrabold text-white font-display">Edit Aircraft Details</h1>
        <p class="text-sm text-slate-400 mt-1">Modify records for aircraft #{{ $airplane->id }} ({{ $airplane->model }})</p>
    </div>

    <div class="glass-card rounded-2xl p-6 border border-slate-900 relative overflow-hidden">
        <form action="{{ route('airplanes.update', $airplane->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Airline Select -->
            <div>
                <label for="airline_id" class="block text-sm font-semibold text-slate-300 mb-2">Operating Airline</label>
                <div class="relative">
                    <select name="airline_id" id="airline_id" required autofocus
                        class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition appearance-none">
                        @foreach($airlines as $airline)
                            <option value="{{ $airline->id }}" {{ old('airline_id', $airplane->airline_id) == $airline->id ? 'selected' : '' }}>{{ $airline->name }} ({{ $airline->code }})</option>
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
                <input type="text" name="model" id="model" value="{{ old('model', $airplane->model) }}" required
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                    placeholder="Boeing 737-800">
                @error('model')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Capacity (Read-Only) -->
            <div>
                <label class="block text-sm font-semibold text-slate-500 mb-2">Seating Capacity (Locked)</label>
                <input type="text" value="{{ $airplane->capacity }} seats" disabled readonly
                    class="block w-full px-4 py-2.5 bg-slate-950/50 border border-slate-900 rounded-xl text-slate-500 text-sm cursor-not-allowed">
                <p class="mt-2 text-[10px] text-slate-500">To alter seating maps and total capacities, please delete this fleet asset and create a new record.</p>
            </div>

            <button type="submit" class="w-full mt-4 py-3 px-4 bg-cyan-500 hover:bg-cyan-400 text-sm font-bold text-white rounded-xl shadow-lg shadow-cyan-950/20 glow-cyan transition">
                Update Aircraft
            </button>
        </form>
    </div>
</div>
@endsection
