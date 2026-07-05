@extends('layouts.app')

@section('title', 'Passenger Manifest - Flight ' . $flight->flight_number)

@section('content')
<div class="py-4">
    <!-- Header with print actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-6 border-b border-slate-900 print:hidden">
        <div>
            <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center text-sm font-medium text-cyan-400 hover:text-cyan-300 transition mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to departures list
            </a>
            <h1 class="text-3xl font-extrabold text-white font-display">Flight Passenger Manifest</h1>
            <p class="text-sm text-slate-400 mt-1">Flight {{ $flight->flight_number }} &bull; {{ $flight->departureAirport->city }} &rarr; {{ $flight->arrivalAirport->city }}</p>
        </div>
        <button onclick="window.print()" class="mt-4 md:mt-0 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-sm font-bold text-cyan-400 hover:text-cyan-300 px-5 py-2.5 rounded-xl transition flex items-center shadow-lg shadow-slate-950/20">
            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print Manifest
        </button>
    </div>

    <!-- PRINT HEADER -->
    <div class="hidden print:block text-center mb-8 border-b border-slate-900 pb-4">
        <h1 class="text-2xl font-bold text-slate-900">Passenger Manifest - Flight {{ $flight->flight_number }}</h1>
        <p class="text-xs text-slate-500">Route: {{ $flight->departureAirport->city }} ({{ $flight->departureAirport->iata_code }}) to {{ $flight->arrivalAirport->city }} ({{ $flight->arrivalAirport->iata_code }})</p>
        <p class="text-xs text-slate-500">Departure: {{ date('d M Y, H:i', strtotime($flight->departure_time)) }} &bull; Aircraft: {{ $flight->airplane->model }}</p>
    </div>

    <!-- Flight Information summary card -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 print:hidden">
        <!-- Aircraft -->
        <div class="glass-card rounded-xl p-5 border border-slate-900">
            <span class="text-[10px] uppercase font-bold text-slate-500 block">Scheduled Aircraft</span>
            <span class="text-sm font-bold text-white block mt-1">{{ $flight->airplane->model }}</span>
            <span class="text-[10px] text-slate-400 block mt-0.5">Capacity: {{ $flight->airplane->capacity }} seats</span>
        </div>
        <!-- Time -->
        <div class="glass-card rounded-xl p-5 border border-slate-900">
            <span class="text-[10px] uppercase font-bold text-slate-500 block">Departure Date</span>
            <span class="text-sm font-bold text-white block mt-1">{{ date('d F Y', strtotime($flight->departure_time)) }}</span>
            <span class="text-[10px] text-slate-400 block mt-0.5">Timing: {{ date('H:i', strtotime($flight->departure_time)) }} &rarr; {{ date('H:i', strtotime($flight->arrival_time)) }}</span>
        </div>
        <!-- Booked count -->
        <div class="glass-card rounded-xl p-5 border border-slate-900">
            <span class="text-[10px] uppercase font-bold text-slate-500 block">Booked Capacity</span>
            <span class="text-sm font-bold text-cyan-400 block mt-1">{{ $passengers->count() }} passengers</span>
            <span class="text-[10px] text-slate-400 block mt-0.5">Occupancy Rate: {{ number_format(($passengers->count() / $flight->airplane->capacity) * 100, 1) }}%</span>
        </div>
        <!-- Status -->
        <div class="glass-card rounded-xl p-5 border border-slate-900">
            <span class="text-[10px] uppercase font-bold text-slate-500 block">Flight Status</span>
            <span class="text-sm font-bold text-emerald-400 block mt-1">Active / Scheduled</span>
            <span class="text-[10px] text-slate-400 block mt-0.5">Gate closes 30 mins before</span>
        </div>
    </div>

    <!-- Passengers Manifest Table -->
    <div class="glass-card rounded-2xl overflow-hidden border border-slate-900 print:border-slate-300">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300 print:text-slate-900">
                <thead class="text-xs uppercase text-slate-500 bg-slate-950/50 border-b border-slate-900 print:bg-slate-100 print:border-slate-300">
                    <tr>
                        <th class="py-4 px-6">Seat</th>
                        <th class="py-4 px-6">Passenger Name</th>
                        <th class="py-4 px-6">Gender</th>
                        <th class="py-4 px-6">Date of Birth</th>
                        <th class="py-4 px-6">NIK / Passport</th>
                        <th class="py-4 px-6 text-right">Order Ref</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/50 print:divide-slate-200">
                    @if($passengers->isEmpty())
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs text-slate-500">No passengers registered for this manifest.</td>
                        </tr>
                    @else
                        @foreach($passengers as $p)
                            <tr class="hover:bg-slate-900/10 transition">
                                <td class="py-4 px-6 font-mono font-bold text-cyan-400 print:text-slate-900 uppercase tracking-widest">{{ $p->seat_number }}</td>
                                <td class="py-4 px-6 font-semibold text-white print:text-slate-900">{{ $p->full_name }}</td>
                                <td class="py-4 px-6 capitalize text-slate-300 print:text-slate-800">{{ $p->gender }}</td>
                                <td class="py-4 px-6 text-slate-400 print:text-slate-700">{{ date('d M Y', strtotime($p->birth_date)) }}</td>
                                <td class="py-4 px-6 font-mono text-slate-400 print:text-slate-700">
                                    {{ substr($p->passport_number, 0, 4) }}******{{ substr($p->passport_number, -4) }}
                                </td>
                                <td class="py-4 px-6 text-right font-mono font-semibold uppercase text-slate-500 print:text-slate-700">
                                    {{ $p->booking->booking_code }}
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
