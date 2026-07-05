@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="py-4">
    <!-- Header -->
    <div class="mb-8 pb-6 border-b border-slate-900">
        <h1 class="text-3xl font-extrabold text-white font-display">System Administration</h1>
        <p class="text-sm text-slate-400 mt-1">Manage airlines, aircraft, airport hubs, and flight schedules</p>
    </div>

    <!-- KPIs grid -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-6 mb-8 text-center">
        <!-- Airports -->
        <div class="glass-card rounded-2xl p-5 border border-slate-900 relative overflow-hidden">
            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Airports</span>
            <span class="block text-2xl font-black text-white mt-1">{{ $airportCount }}</span>
            <a href="{{ route('airports.index') }}" class="block text-[10px] text-cyan-400 font-bold mt-2 hover:underline">Manage &rarr;</a>
        </div>

        <!-- Airlines -->
        <div class="glass-card rounded-2xl p-5 border border-slate-900 relative overflow-hidden">
            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Airlines</span>
            <span class="block text-2xl font-black text-white mt-1">{{ $airlineCount }}</span>
            <a href="{{ route('airlines.index') }}" class="block text-[10px] text-cyan-400 font-bold mt-2 hover:underline">Manage &rarr;</a>
        </div>

        <!-- Airplanes -->
        <div class="glass-card rounded-2xl p-5 border border-slate-900 relative overflow-hidden">
            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Airplanes</span>
            <span class="block text-2xl font-black text-white mt-1">{{ $airplaneCount }}</span>
            <a href="{{ route('airplanes.index') }}" class="block text-[10px] text-cyan-400 font-bold mt-2 hover:underline">Manage &rarr;</a>
        </div>

        <!-- Flights -->
        <div class="glass-card rounded-2xl p-5 border border-slate-900 relative overflow-hidden">
            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Flights</span>
            <span class="block text-2xl font-black text-white mt-1">{{ $flightCount }}</span>
            <a href="{{ route('flights.index') }}" class="block text-[10px] text-cyan-400 font-bold mt-2 hover:underline">Manage &rarr;</a>
        </div>

        <!-- Bookings -->
        <div class="glass-card rounded-2xl p-5 border border-slate-900 relative overflow-hidden col-span-2 md:col-span-1">
            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Total Bookings</span>
            <span class="block text-2xl font-black text-white mt-1">{{ $bookingCount }}</span>
            <span class="block text-[10px] text-slate-500 mt-2">All Statuses</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Navigation Grid (Quick Links) -->
        <div class="lg:col-span-4 space-y-4">
            <h3 class="font-bold text-white text-sm uppercase tracking-wider mb-2">Administrative Hubs</h3>
            
            <a href="{{ route('airports.index') }}" class="flex items-center justify-between p-4 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl transition duration-150 group">
                <div class="flex items-center space-x-3">
                    <i data-lucide="map-pin" class="w-5 h-5 text-cyan-400"></i>
                    <span class="text-sm font-semibold text-slate-200">Airports Directory</span>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-500 group-hover:text-cyan-400 transition"></i>
            </a>

            <a href="{{ route('airlines.index') }}" class="flex items-center justify-between p-4 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl transition duration-150 group">
                <div class="flex items-center space-x-3">
                    <i data-lucide="plane" class="w-5 h-5 text-cyan-400"></i>
                    <span class="text-sm font-semibold text-slate-200">Airlines Management</span>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-500 group-hover:text-cyan-400 transition"></i>
            </a>

            <a href="{{ route('airplanes.index') }}" class="flex items-center justify-between p-4 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl transition duration-150 group">
                <div class="flex items-center space-x-3">
                    <i data-lucide="anchor" class="w-5 h-5 text-cyan-400"></i>
                    <span class="text-sm font-semibold text-slate-200">Armada Airplanes & Seats</span>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-500 group-hover:text-cyan-400 transition"></i>
            </a>

            <a href="{{ route('flights.index') }}" class="flex items-center justify-between p-4 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl transition duration-150 group">
                <div class="flex items-center space-x-3">
                    <i data-lucide="calendar" class="w-5 h-5 text-cyan-400"></i>
                    <span class="text-sm font-semibold text-slate-200">Schedules & Route Creator</span>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-500 group-hover:text-cyan-400 transition"></i>
            </a>
        </div>

        <!-- Recent Transactions -->
        <div class="lg:col-span-8">
            <div class="glass-card rounded-2xl p-6 border border-slate-900 h-full flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-white text-sm uppercase tracking-wider mb-4">Recent Passenger Bookings</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-300">
                            <thead class="text-xs uppercase text-slate-500 border-b border-slate-900">
                                <tr>
                                    <th class="py-3 px-2">Code</th>
                                    <th class="py-3 px-2">Customer</th>
                                    <th class="py-3 px-2">Price</th>
                                    <th class="py-3 px-2 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/50">
                                @if($recentBookings->isEmpty())
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-xs text-slate-500">No bookings logged yet</td>
                                    </tr>
                                @else
                                    @foreach($recentBookings as $booking)
                                        <tr>
                                            <td class="py-3.5 px-2 font-mono font-bold text-white uppercase">{{ $booking->booking_code }}</td>
                                            <td class="py-3.5 px-2">{{ $booking->user->name }}</td>
                                            <td class="py-3.5 px-2 text-cyan-400 font-bold">IDR {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                            <td class="py-3.5 px-2 text-right">
                                                <span class="text-[10px] font-bold px-2 py-0.5 uppercase rounded {{ $booking->status === 'confirmed' ? 'bg-emerald-500/20 text-emerald-400' : ($booking->status === 'waiting' ? 'bg-blue-500/20 text-blue-400' : ($booking->status === 'pending' ? 'bg-amber-500/20 text-amber-400' : 'bg-red-500/20 text-red-400')) }}">
                                                    {{ $booking->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
