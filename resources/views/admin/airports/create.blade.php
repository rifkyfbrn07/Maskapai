@extends('layouts.app')

@section('title', 'Add Airport')

@section('content')
<div class="max-w-xl mx-auto py-4">
    <div class="mb-8 pb-6 border-b border-slate-900">
        <a href="{{ route('airports.index') }}" class="inline-flex items-center text-sm font-medium text-cyan-400 hover:text-cyan-300 transition mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to list
        </a>
        <h1 class="text-3xl font-extrabold text-white font-display">Add New Airport</h1>
        <p class="text-sm text-slate-400 mt-1">Register a new aviation destination hub in Indonesia</p>
    </div>

    <div class="glass-card rounded-2xl p-6 border border-slate-900 relative overflow-hidden">
        <form action="{{ route('airports.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Airport Name -->
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-300 mb-2">Airport Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                    placeholder="Soekarno-Hatta International Airport">
                @error('name')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- IATA Code -->
            <div>
                <label for="iata_code" class="block text-sm font-semibold text-slate-300 mb-2">IATA Code (3 Characters)</label>
                <input type="text" name="iata_code" id="iata_code" value="{{ old('iata_code') }}" required maxlength="3"
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm font-mono tracking-widest focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                    placeholder="CGK">
                @error('iata_code')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- City -->
            <div>
                <label for="city" class="block text-sm font-semibold text-slate-300 mb-2">City</label>
                <input type="text" name="city" id="city" value="{{ old('city') }}" required
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                    placeholder="Jakarta">
                @error('city')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Country -->
            <div>
                <label for="country" class="block text-sm font-semibold text-slate-300 mb-2">Country</label>
                <input type="text" name="country" id="country" value="{{ old('country', 'Indonesia') }}" required
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition">
                @error('country')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full mt-4 py-3 px-4 bg-cyan-500 hover:bg-cyan-400 text-sm font-bold text-white rounded-xl shadow-lg shadow-cyan-950/20 glow-cyan transition">
                Create Airport
            </button>
        </form>
    </div>
</div>
@endsection
