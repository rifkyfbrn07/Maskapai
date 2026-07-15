@extends('layouts.app')

@section('title', 'Staff Operations')

@section('content')
<div class="py-4">
    <!-- Header -->
    <div class="mb-8 pb-6 border-b border-slate-900">
        <h1 class="text-3xl font-extrabold text-white font-display">Ticketing & Manifest Operations</h1>
        <p class="text-sm text-slate-400 mt-1">Monitor passenger lists and print flight manifests for active departures</p>
    </div>

    <!-- Awaiting Confirmation Bookings -->
    <div class="mb-10">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center">
            <i data-lucide="clock" class="w-5 h-5 mr-2 text-amber-500"></i> Bookings Awaiting Confirmation
            @if($waitingBookings->count() > 0)
                <span class="ml-2 px-2 py-0.5 bg-amber-500/10 border border-amber-500/25 text-amber-400 font-bold font-mono rounded-full text-xs">
                    {{ $waitingBookings->count() }} new
                </span>
            @endif
        </h2>
        
        <div class="glass-card rounded-2xl overflow-hidden border border-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase text-slate-500 bg-slate-950/50 border-b border-slate-900">
                        <tr>
                            <th class="py-4 px-6">PNR Code</th>
                            <th class="py-4 px-6">Customer</th>
                            <th class="py-4 px-6">Flight & Route</th>
                            <th class="py-4 px-6">Passengers / Price</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/50">
                        @if($waitingBookings->isEmpty())
                            <tr>
                                <td colspan="5" class="py-8 text-center text-xs text-slate-500">No bookings currently awaiting confirmation.</td>
                            </tr>
                        @else
                            @foreach($waitingBookings as $booking)
                                <tr class="hover:bg-slate-900/10 transition">
                                    <td class="py-4 px-6 font-mono font-bold text-white uppercase tracking-wider">
                                        {{ $booking->booking_code }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="block font-semibold text-slate-200 leading-tight">{{ $booking->user->name }}</span>
                                        <span class="block text-xs text-slate-500 mt-0.5">{{ $booking->user->email }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="font-semibold text-slate-300 block">Flight {{ $booking->flight->flight_number }}</span>
                                        <span class="text-xs text-slate-500 block mt-0.5">{{ $booking->flight->departureAirport->city }} &rarr; {{ $booking->flight->arrivalAirport->city }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="block text-slate-300 font-medium">{{ $booking->total_passengers }} passenger(s)</span>
                                        <span class="block text-xs font-bold text-cyan-400 mt-0.5">IDR {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-right space-x-2">
                                        <form action="{{ route('staff.bookings.confirm', $booking->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center py-1.5 px-3.5 bg-emerald-500 hover:bg-emerald-400 text-xs font-bold text-white rounded-lg transition shadow-md shadow-emerald-950/20">
                                                <i data-lucide="check" class="w-3.5 h-3.5 mr-1"></i> Confirm
                                            </button>
                                        </form>
                                        <form action="{{ route('staff.bookings.cancel', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel/reject this booking?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center py-1.5 px-3.5 bg-red-500 hover:bg-red-400 text-xs font-bold text-white rounded-lg transition shadow-md shadow-red-950/20">
                                                <i data-lucide="x" class="w-3.5 h-3.5 mr-1"></i> Reject
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

    <!-- Active Flights List -->
    <div>
        <h2 class="text-xl font-bold text-white mb-4 flex items-center">
            <i data-lucide="plane" class="w-5 h-5 mr-2 text-cyan-400"></i> Active Departures
        </h2>
        <div class="glass-card rounded-2xl overflow-hidden border border-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase text-slate-500 bg-slate-950/50 border-b border-slate-900">
                    <tr>
                        <th class="py-4 px-6">Flight No.</th>
                        <th class="py-4 px-6">Carrier / Model</th>
                        <th class="py-4 px-6">Route</th>
                        <th class="py-4 px-6">Departure Time</th>
                        <th class="py-4 px-6 text-center">Active Manifest</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/50">
                    @if($flights->isEmpty())
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs text-slate-500">No flights scheduled.</td>
                        </tr>
                    @else
                        @foreach($flights as $flight)
                            <tr class="hover:bg-slate-900/10 transition">
                                <td class="py-4 px-6 font-mono font-bold text-cyan-400 uppercase tracking-widest">{{ $flight->flight_number }}</td>
                                <td class="py-4 px-6">
                                    <span class="block font-semibold text-white leading-tight">{{ $flight->airplane->airline->name }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">{{ $flight->airplane->model }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-bold text-sm text-slate-300 block">{{ $flight->departureAirport->iata_code }} &rarr; {{ $flight->arrivalAirport->iata_code }}</span>
                                    <span class="text-[10px] text-slate-500 uppercase tracking-wider block mt-0.5">{{ $flight->departureAirport->city }} &rarr; {{ $flight->arrivalAirport->city }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="block text-white font-semibold">{{ date('d M Y', strtotime($flight->departure_time)) }}</span>
                                    <span class="block text-xs text-slate-400 font-mono mt-0.5">{{ date('H:i', strtotime($flight->departure_time)) }}</span>
                                    <span class="mt-2 inline-flex items-center text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-cyan-500/15 text-cyan-300">
                                        {{ $flight->statusLabel() }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3 py-1 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-bold font-mono rounded-lg text-xs">
                                        {{ $flight->active_bookings_count }} booked
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <a href="{{ route('staff.flight_manifest', $flight->id) }}"
                                        class="inline-flex items-center py-2 px-4 bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-cyan-500/20 text-xs font-bold text-cyan-400 hover:text-cyan-300 rounded-xl transition">
                                        <i data-lucide="users" class="w-3.5 h-3.5 mr-1.5"></i> Flight Manifest
                                    </a>
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
