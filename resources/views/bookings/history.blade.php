@extends('layouts.app')

@section('title', 'My Bookings')

@section('content')
<div class="max-w-4xl mx-auto py-4">
    <div class="mb-8 pb-6 border-b border-slate-900 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold text-white font-display">My Bookings</h1>
            <p class="text-sm text-slate-400 mt-1">Trace all your flight tickets and check-in manifests</p>
        </div>
        <a href="{{ route('home') }}" class="bg-cyan-500 hover:bg-cyan-400 text-xs font-bold text-white px-4 py-2.5 rounded-full transition shadow-md shadow-cyan-950/20">
            Book New Flight
        </a>
    </div>

    @if($bookings->isEmpty())
        <div class="glass-card rounded-3xl p-12 text-center text-slate-400">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-900 border border-slate-800 text-slate-500 rounded-2xl mb-4">
                <i data-lucide="ticket-minus" class="w-8 h-8"></i>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">No Bookings Found</h3>
            <p class="text-sm text-slate-400 mb-6">You haven't made any flight reservations yet. Start searching and booking today!</p>
            <a href="{{ route('home') }}" class="inline-flex items-center py-2.5 px-6 bg-cyan-500 hover:bg-cyan-400 text-white font-semibold rounded-full transition shadow-lg shadow-cyan-950/20">
                Search Flights
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($bookings as $booking)
                <div class="glass-card rounded-2xl p-6 border border-slate-900 transition hover:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    
                    <!-- Left: Booking status and Flight details -->
                    <div class="flex-grow space-y-3">
                        <div class="flex items-center space-x-3">
                            <span class="font-mono font-bold text-white px-3 py-1 bg-slate-950 border border-slate-850 rounded-lg text-sm uppercase tracking-wider">
                                {{ $booking->booking_code }}
                            </span>
                            <span class="text-xs font-semibold px-2.5 py-0.5 uppercase rounded {{ $booking->status === 'confirmed' ? 'bg-emerald-500/20 text-emerald-400' : ($booking->status === 'waiting' ? 'bg-blue-500/20 text-blue-400' : ($booking->status === 'pending' ? 'bg-amber-500/20 text-amber-400' : 'bg-red-500/20 text-red-400')) }}">
                                {{ $booking->status === 'waiting' ? 'waiting confirmation' : $booking->status }}
                            </span>
                            <span class="text-xs text-slate-500">{{ date('d M Y, H:i', strtotime($booking->created_at)) }}</span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <!-- Logo -->
                            <div class="w-8 h-8 bg-white/5 border border-slate-850 rounded-lg flex items-center justify-center p-1.5">
                                @if($booking->flight->airplane->airline->logo)
                                    <img src="{{ $booking->flight->airplane->airline->logo }}" alt="" class="max-w-full max-h-full object-contain">
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-sm">
                                    {{ $booking->flight->departureAirport->city }} ({{ $booking->flight->departureAirport->iata_code }}) &rarr; {{ $booking->flight->arrivalAirport->city }} ({{ $booking->flight->arrivalAirport->iata_code }})
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Flight {{ $booking->flight->flight_number }} &bull; {{ date('D, d F Y', strtotime($booking->flight->departure_time)) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Pricing and Actions -->
                    <div class="flex flex-row md:flex-col justify-between md:items-end gap-4 border-t border-slate-900 md:border-t-0 pt-4 md:pt-0 pl-0 md:pl-6 md:border-l md:border-slate-850 flex-shrink-0">
                        <div class="text-left md:text-right">
                            <span class="text-[10px] text-slate-500 uppercase tracking-wider block">Total Amount</span>
                            <span class="text-lg font-black text-cyan-400">IDR {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-slate-500 block mt-0.5">{{ $booking->passengers->count() }} passenger(s)</span>
                        </div>

                        <div>
                            @if($booking->status === 'confirmed')
                                <a href="{{ route('eticket.show', $booking->booking_code) }}"
                                    class="inline-flex items-center py-2 px-4 bg-cyan-500 hover:bg-cyan-400 text-xs font-bold text-white rounded-xl transition shadow-md shadow-cyan-950/20">
                                    <i data-lucide="ticket" class="w-3.5 h-3.5 mr-1.5"></i> View E-Ticket
                                </a>
                            @elseif($booking->status === 'waiting')
                                <span class="inline-flex items-center text-xs text-blue-400 font-semibold px-4 py-2 border border-blue-950/30 bg-blue-950/10 rounded-xl">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5"></i> Waiting Confirmation
                                </span>
                            @elseif($booking->status === 'pending')
                                <a href="{{ route('bookings.checkout', $booking->id) }}"
                                    class="inline-flex items-center py-2 px-4 bg-amber-500 hover:bg-amber-400 text-xs font-bold text-slate-950 rounded-xl transition">
                                    <i data-lucide="wallet" class="w-3.5 h-3.5 mr-1.5"></i> Complete Payment
                                </a>
                            @else
                                <span class="inline-flex items-center text-xs text-slate-500 font-semibold px-4 py-2 border border-slate-850 bg-slate-900/30 rounded-xl">
                                    Cancelled
                                </span>
                            @endif
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
