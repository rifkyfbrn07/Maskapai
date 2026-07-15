@extends('layouts.app')

@section('title', 'Flights Scheduling')

@section('content')
<div class="py-4">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-6 border-b border-slate-900">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-cyan-400 hover:text-cyan-300 transition mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to admin dashboard
            </a>
            <h1 class="text-3xl font-extrabold text-white font-display">Flights Scheduling</h1>
            <p class="text-sm text-slate-400 mt-1">Schedule new routes, set base pricing, and check seating availability</p>
        </div>
        <a href="{{ route('flights.create') }}" class="mt-4 md:mt-0 bg-cyan-500 hover:bg-cyan-400 text-sm font-bold text-white px-5 py-2.5 rounded-xl transition flex items-center shadow-lg shadow-cyan-950/20 text-center">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Schedule Flight
        </a>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-2xl overflow-hidden border border-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase text-slate-500 bg-slate-950/50 border-b border-slate-900">
                    <tr>
                        <th class="py-4 px-6">Flight No.</th>
                        <th class="py-4 px-6">Carrier / Aircraft</th>
                        <th class="py-4 px-6">Route</th>
                        <th class="py-4 px-6">Timing</th>
                        <th class="py-4 px-6">Price</th>
                        <th class="py-4 px-6">Capacity</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/50">
                    @if($flights->isEmpty())
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-slate-500">No scheduled flights registered.</td>
                        </tr>
                    @else
                        @foreach($flights as $flight)
                            <tr class="hover:bg-slate-900/10 transition">
                                <td class="py-4 px-6 font-mono font-bold text-cyan-400 uppercase tracking-widest">{{ $flight->flight_number }}</td>
                                <td class="py-4 px-6">
                                    <span class="block font-semibold text-white">{{ $flight->airplane->airline->name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $flight->airplane->model }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-bold block text-sm text-slate-300">{{ $flight->departureAirport->iata_code }} &rarr; {{ $flight->arrivalAirport->iata_code }}</span>
                                    <span class="text-[10px] text-slate-500 uppercase tracking-wider block mt-0.5">{{ $flight->departureAirport->city }} &rarr; {{ $flight->arrivalAirport->city }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="block text-white font-semibold">{{ date('d M Y', strtotime($flight->departure_time)) }}</span>
                                    <span class="block text-xs text-slate-400 font-mono mt-0.5">{{ date('H:i', strtotime($flight->departure_time)) }} - {{ date('H:i', strtotime($flight->arrival_time)) }}</span>
                                    <span class="mt-2 inline-flex items-center text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full {{ $flight->isCompleted() ? 'bg-slate-500/15 text-slate-300' : 'bg-cyan-500/15 text-cyan-300' }}">
                                        {{ $flight->statusLabel() }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-bold text-cyan-400 font-mono">IDR {{ number_format($flight->price, 0, ',', '.') }}</td>
                                <td class="py-4 px-6">
                                    <span class="font-semibold block text-slate-300 font-mono">{{ $flight->available_seats }} / {{ $flight->airplane->capacity }}</span>
                                    <span class="text-[10px] uppercase font-bold tracking-wider {{ $flight->available_seats <= 0 ? 'text-red-500' : 'text-slate-500' }}">
                                        {{ $flight->available_seats <= 0 ? 'Sold Out' : 'seats left' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right flex justify-end space-x-3 items-center">
                                    <a href="{{ route('flights.edit', $flight->id) }}" class="p-1.5 bg-slate-900 border border-slate-800 text-slate-400 hover:text-cyan-400 rounded-lg hover:border-cyan-500/20 transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('flights.destroy', $flight->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this flight schedule?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-slate-900 border border-slate-800 text-slate-400 hover:text-red-400 rounded-lg hover:border-red-500/20 transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
