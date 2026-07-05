@extends('layouts.app')

@section('title', 'E-Ticket Boarding Pass')

@section('styles')
<style>
    /* Styling ticket cutouts and indicators */
    .ticket-cutout-left {
        left: -12px;
        border-radius: 0 12px 12px 0;
    }
    .ticket-cutout-right {
        right: -12px;
        border-radius: 12px 0 0 12px;
    }
    @media print {
        header, footer, .print\:hidden, .alert, button, form, .fixed, .mb-6 {
            display: none !important;
        }
        body, html, main {
            background: white !important;
            color: black !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .glass-card {
            background: white !important;
            border: 2px solid #0f172a !important;
            color: black !important;
            box-shadow: none !important;
            border-radius: 20px !important;
        }
        .text-white, h3, h4, span, p, div {
            color: #0f172a !important;
        }
        .text-slate-400, .text-slate-500, .text-slate-600 {
            color: #475569 !important;
        }
        .text-cyan-400, .text-cyan-500 {
            color: #0284c7 !important;
        }
        .bg-slate-950\/20, .bg-slate-950\/50, .bg-slate-900, .bg-slate-950\/20, .bg-slate-950\/40, .bg-white\/5 {
            background: transparent !important;
            border-color: #cbd5e1 !important;
        }
        .border-slate-900, .border-slate-800, .border-slate-850, .border-dashed {
            border-color: #94a3b8 !important;
        }
        #qrcode-container {
            border: 1px solid #94a3b8 !important;
        }
        .max-w-2xl {
            max-width: 100% !important;
            width: 100% !important;
        }
    }
</style>
@endsection

@section('content')
<div class="max-w-2xl mx-auto py-6">
    
    <!-- Offline Indicator -->
    <div class="mb-6 flex justify-between items-center bg-slate-900/40 border border-slate-800 rounded-2xl px-5 py-3 text-sm">
        <div class="flex items-center text-slate-300">
            <span class="relative flex h-2 w-2 mr-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span>
            </span>
            <span>Local caching active</span>
        </div>
        <span class="px-3 py-1 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-bold rounded-lg text-xs flex items-center">
            <i data-lucide="shield-check" class="w-3.5 h-3.5 mr-1"></i> Offline Secured
        </span>
    </div>

    <!-- Main Boarding Pass Card -->
    <div class="glass-card rounded-3xl glow-cyan relative overflow-hidden">
        
        <!-- Ticket Header (Carrier information) -->
        <div class="bg-gradient-to-r from-cyan-950/40 to-blue-950/40 px-8 py-6 border-b border-slate-900 flex justify-between items-center relative">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-500 to-blue-500"></div>
            
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/5 border border-slate-850 rounded-xl flex items-center justify-center p-1.5">
                    @if($booking->flight->airplane->airline->logo)
                        <img src="{{ $booking->flight->airplane->airline->logo }}" alt="" class="max-w-full max-h-full object-contain">
                    @endif
                </div>
                <div>
                    <h3 class="font-extrabold text-white leading-tight font-display tracking-tight text-lg">{{ $booking->flight->airplane->airline->name }}</h3>
                    <p class="text-[10px] text-slate-500 font-semibold tracking-widest uppercase">Boarding Pass</p>
                </div>
            </div>

            <div class="text-right">
                <span class="block text-[10px] text-slate-500 uppercase tracking-wider">PNR Code</span>
                <span class="block font-mono font-bold text-cyan-400 text-lg uppercase tracking-widest">{{ $booking->booking_code }}</span>
            </div>
        </div>

        <!-- Ticket Body (Flight details) -->
        <div class="px-8 py-8 space-y-6 relative border-b border-dashed border-slate-800">
            <!-- Cutouts -->
            <div class="absolute bottom-[-12px] ticket-cutout-left w-6 h-6 bg-slate-950 border border-slate-900 z-10"></div>
            <div class="absolute bottom-[-12px] ticket-cutout-right w-6 h-6 bg-slate-950 border border-slate-900 z-10"></div>

            <div class="grid grid-cols-3 gap-6 items-center">
                <!-- Origin -->
                <div>
                    <span class="block text-3xl font-black text-white leading-none font-display">{{ $booking->flight->departureAirport->iata_code }}</span>
                    <span class="block text-xs font-semibold text-slate-400 mt-1 uppercase">{{ $booking->flight->departureAirport->city }}</span>
                    <span class="block text-[10px] text-slate-500 truncate mt-0.5">{{ $booking->flight->departureAirport->name }}</span>
                </div>
                <!-- Connection Icon -->
                <div class="flex flex-col items-center justify-center text-slate-600">
                    <span class="text-[10px] text-slate-500 font-mono mb-1">{{ date('d M Y', strtotime($booking->flight->departure_time)) }}</span>
                    <div class="w-full flex items-center justify-center relative">
                        <div class="w-full h-0.5 bg-slate-800"></div>
                        <i data-lucide="plane" class="w-5 h-5 text-cyan-500 absolute bg-slate-950/50 p-0.5 rounded-full border border-slate-850 rotate-90"></i>
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1">Direct Flight</span>
                </div>
                <!-- Destination -->
                <div class="text-right">
                    <span class="block text-3xl font-black text-white leading-none font-display">{{ $booking->flight->arrivalAirport->iata_code }}</span>
                    <span class="block text-xs font-semibold text-slate-400 mt-1 uppercase">{{ $booking->flight->arrivalAirport->city }}</span>
                    <span class="block text-[10px] text-slate-500 truncate mt-0.5">{{ $booking->flight->arrivalAirport->name }}</span>
                </div>
            </div>

            <!-- Schedule info grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-4 border-t border-slate-900/50 text-sm">
                <div>
                    <span class="text-slate-500 block text-xs">Departure Time</span>
                    <span class="text-white font-bold">{{ date('H:i', strtotime($booking->flight->departure_time)) }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block text-xs">Arrival Time</span>
                    <span class="text-white font-bold">{{ date('H:i', strtotime($booking->flight->arrival_time)) }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block text-xs">Aircraft Model</span>
                    <span class="text-white font-bold truncate block">{{ $booking->flight->airplane->model }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block text-xs">Flight Number</span>
                    <span class="text-white font-bold font-mono uppercase">{{ $booking->flight->flight_number }}</span>
                </div>
            </div>
        </div>

        <!-- Passengers Manifest & QR Code -->
        <div class="px-8 py-8 bg-slate-950/20 flex flex-col md:flex-row items-center justify-between gap-8">
            <!-- Passenger manifests -->
            <div class="w-full space-y-4">
                <h4 class="font-bold text-white text-xs uppercase tracking-widest text-slate-400 mb-2">Passenger Manifest</h4>
                
                <div class="space-y-3 max-h-48 overflow-y-auto pr-2">
                    @foreach($booking->passengers as $p)
                        <div class="flex items-center justify-between p-3 bg-slate-950/50 border border-slate-900 rounded-xl">
                            <div>
                                <span class="block text-sm font-bold text-white">{{ $p->full_name }}</span>
                                <span class="block text-[10px] text-slate-500 uppercase">NIK/Passport: {{ substr($p->passport_number, 0, 4) }}******{{ substr($p->passport_number, -4) }}</span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[10px] text-slate-500 uppercase">Seat</span>
                                <span class="px-3 py-1 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-mono font-bold rounded-lg text-xs inline-block">
                                    {{ $p->seat_number }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Dynamic QR Code (Offline support) -->
            <div class="flex-shrink-0 flex flex-col items-center justify-center p-4 bg-slate-900 border border-slate-800 rounded-3xl">
                <!-- QR target -->
                <div id="qrcode-container" class="bg-white p-2 rounded-2xl"></div>
                <span class="mt-3 text-[10px] font-mono text-slate-400 uppercase tracking-widest font-semibold">Boarding Scan</span>
            </div>
        </div>

        <!-- Printing details footer -->
        <div class="bg-slate-950/40 border-t border-slate-900 py-4 px-8 flex justify-between items-center text-xs text-slate-500 print:hidden">
            <span>Booked by: {{ $booking->user->name }}</span>
            <button onclick="window.print()" class="text-cyan-400 hover:text-cyan-300 font-bold transition flex items-center">
                <i data-lucide="printer" class="w-3.5 h-3.5 mr-1.5"></i> Print Boarding Pass
            </button>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<!-- QR Code Offline generator script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
        // Generate QR Code Offline
        new QRCode(document.getElementById("qrcode-container"), {
            text: "{{ $booking->booking_code }}",
            width: 120,
            height: 120,
            colorDark : "#0f172a",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });

        // Register Service Worker for PWA Offline Caching
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then((registration) => {
                    console.log('Service Worker registered with scope: ', registration.scope);
                    
                    // Force the service worker to cache this specific e-ticket page for offline loading
                    if (caches) {
                        caches.open('flyindonesia-pwa-cache-v1').then((cache) => {
                            cache.add(window.location.href)
                                .then(() => console.log('E-Ticket cached for offline access!'))
                                .catch(err => console.error('Cache failed: ', err));
                        });
                    }
                })
                .catch((err) => {
                    console.error('Service Worker registration failed: ', err);
                });
        }
    });
</script>
@endsection
