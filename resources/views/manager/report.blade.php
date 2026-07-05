@extends('layouts.app')

@section('title', 'Sales Report')

@section('styles')
<style>
    @media print {
        /* Hide layout items */
        header, footer, .print\:hidden, .alert, button, form, .fixed {
            display: none !important;
        }
        body, html, main {
            background: white !important;
            color: black !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .glass-card {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            backdrop-filter: none !important;
            color: black !important;
        }
        table {
            border-collapse: collapse !important;
            width: 100% !important;
            color: black !important;
        }
        th, td {
            border: 1px solid #cbd5e1 !important;
            color: black !important;
            padding: 8px !important;
        }
        th {
            background-color: #f1f5f9 !important;
        }
        .print-header {
            display: block !important;
        }
        .print-footer {
            display: flex !important;
        }
    }
</style>
@endsection

@section('content')
<div class="py-4">
    <!-- Header with actions (Screen only) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-6 border-b border-slate-900 print:hidden">
        <div>
            <h1 class="text-3xl font-extrabold text-white font-display">Sales & Revenue Report</h1>
            <p class="text-sm text-slate-400 mt-1">Generate, filter and print formal sales report sheets for management</p>
        </div>
        <div class="flex space-x-3 mt-4 md:mt-0">
            <a href="{{ route('manager.dashboard') }}" class="bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs font-bold text-slate-300 px-5 py-2.5 rounded-xl transition flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i> Back to Dashboard
            </a>
            <button onclick="window.print()" class="bg-cyan-500 hover:bg-cyan-400 text-xs font-bold text-white px-5 py-2.5 rounded-xl transition flex items-center shadow-lg shadow-cyan-950/20 glow-cyan">
                <i data-lucide="printer" class="w-4 h-4 mr-1.5"></i> Print / Export PDF
            </button>
        </div>
    </div>

    <!-- Official Report Header (Print only) -->
    <div class="hidden print:block text-center mb-8 border-b-2 border-double border-slate-800 pb-6">
        <h1 class="text-3xl font-black text-slate-900 font-display tracking-tight uppercase">FlyIndonesia</h1>
        <p class="text-xs text-slate-500 font-mono mt-1">FLYINDONESIA TICKET RESERVATION SYSTEM</p>
        <p class="text-xs text-slate-500 font-mono">Jl. Penerbangan No. 45, Jakarta - Indonesia</p>
        <h2 class="text-xl font-bold text-slate-950 mt-6 uppercase tracking-wider">Laporan Penjualan Tiket Maskapai</h2>
        <p class="text-sm text-slate-650 mt-1">
            Periode: <strong>{{ date('d F Y', strtotime($startDate)) }}</strong> s/d <strong>{{ date('d F Y', strtotime($endDate)) }}</strong>
        </p>
        @if($airlineId && $airlines->firstWhere('id', $airlineId))
            <p class="text-xs text-slate-650 mt-0.5">Maskapai: <strong>{{ $airlines->firstWhere('id', $airlineId)->name }}</strong></p>
        @endif
    </div>

    <!-- Filter Form (Screen only) -->
    <div class="glass-card rounded-2xl p-6 border border-slate-900 mb-8 print:hidden">
        <h3 class="font-bold text-white text-sm uppercase tracking-wider mb-4 flex items-center">
            <i data-lucide="filter" class="w-4 h-4 mr-2 text-cyan-400"></i> Filter Report Criteria
        </h3>
        <form action="{{ route('manager.report') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label for="start_date" class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/50">
            </div>
            <div>
                <label for="end_date" class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/50">
            </div>
            <div>
                <label for="airline_id" class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Airline Filter</label>
                <select name="airline_id" id="airline_id"
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-300 focus:outline-none focus:border-cyan-500/50">
                    <option value="">All Airlines</option>
                    @foreach($airlines as $airline)
                        <option value="{{ $airline->id }}" {{ $airlineId == $airline->id ? 'selected' : '' }}>
                            {{ $airline->name }} ({{ $airline->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="flex-grow bg-cyan-500 hover:bg-cyan-400 text-sm font-bold text-white py-3 rounded-xl transition">
                    Apply Filter
                </button>
                <a href="{{ route('manager.report') }}" class="bg-slate-900 hover:bg-slate-800 border border-slate-850 text-slate-300 p-3 rounded-xl transition" title="Reset filters">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Report Table -->
    <div class="glass-card rounded-2xl overflow-hidden border border-slate-900 print:border-none print:shadow-none print:bg-transparent">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300 print:text-slate-950">
                <thead class="text-xs uppercase text-slate-500 bg-slate-950/50 border-b border-slate-900 print:bg-slate-100 print:border-slate-300 print:text-slate-900">
                    <tr>
                        <th class="py-4 px-6 print:py-2 print:px-3">No.</th>
                        <th class="py-4 px-6 print:py-2 print:px-3">PNR Code</th>
                        <th class="py-4 px-6 print:py-2 print:px-3">Customer</th>
                        <th class="py-4 px-6 print:py-2 print:px-3">Flight Info</th>
                        <th class="py-4 px-6 print:py-2 print:px-3">Route</th>
                        <th class="py-4 px-6 print:py-2 print:px-3">Date</th>
                        <th class="py-4 px-6 print:py-2 print:px-3 text-center">Pax</th>
                        <th class="py-4 px-6 print:py-2 print:px-3 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/50 print:divide-slate-300">
                    @if($bookings->isEmpty())
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-slate-500 print:text-slate-700">No transactions recorded for the selected criteria.</td>
                        </tr>
                    @else
                        @foreach($bookings as $index => $booking)
                            <tr class="hover:bg-slate-900/10 transition print:hover:bg-transparent">
                                <td class="py-4 px-6 print:py-2 print:px-3 text-xs text-slate-400 print:text-slate-700">{{ $index + 1 }}</td>
                                <td class="py-4 px-6 print:py-2 print:px-3 font-mono font-bold text-white print:text-slate-900 uppercase">{{ $booking->booking_code }}</td>
                                <td class="py-4 px-6 print:py-2 print:px-3">
                                    <span class="block font-semibold text-slate-200 print:text-slate-900">{{ $booking->user->name }}</span>
                                    <span class="block text-[10px] text-slate-500 print:text-slate-500 mt-0.5">{{ $booking->user->email }}</span>
                                </td>
                                <td class="py-4 px-6 print:py-2 print:px-3 font-mono text-xs">
                                    <span class="font-bold block text-slate-300 print:text-slate-900">{{ $booking->flight->flight_number }}</span>
                                    <span class="text-[10px] text-slate-500 block mt-0.5">{{ $booking->flight->airplane->airline->name }}</span>
                                </td>
                                <td class="py-4 px-6 print:py-2 print:px-3">
                                    <span class="font-bold text-slate-300 print:text-slate-900 block text-xs">{{ $booking->flight->departureAirport->iata_code }} &rarr; {{ $booking->flight->arrivalAirport->iata_code }}</span>
                                    <span class="text-[10px] text-slate-500 block mt-0.5">{{ $booking->flight->departureAirport->city }} &rarr; {{ $booking->flight->arrivalAirport->city }}</span>
                                </td>
                                <td class="py-4 px-6 print:py-2 print:px-3 text-xs text-slate-400 print:text-slate-700">
                                    {{ date('d M Y, H:i', strtotime($booking->created_at)) }}
                                </td>
                                <td class="py-4 px-6 print:py-2 print:px-3 text-center font-bold text-xs">{{ $booking->total_passengers }}</td>
                                <td class="py-4 px-6 print:py-2 print:px-3 text-right font-black text-cyan-400 print:text-slate-950 font-mono">
                                    IDR {{ number_format($booking->total_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <!-- Summary Row -->
                <tfoot class="bg-slate-950/80 border-t border-slate-900 font-bold print:bg-slate-50 print:border-slate-300">
                    <tr>
                        <td colspan="6" class="py-4 px-6 print:py-3 print:px-3 text-right text-slate-450 print:text-slate-700 uppercase tracking-widest text-xs">Total Revenue Summary</td>
                        <td class="py-4 px-6 print:py-3 print:px-3 text-center text-white print:text-slate-900 font-mono">{{ $bookings->sum('total_passengers') }}</td>
                        <td class="py-4 px-6 print:py-3 print:px-3 text-right text-cyan-400 print:text-slate-950 font-mono text-base">
                            IDR {{ number_format($totalRevenue, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Official Report Footer (Print only) -->
    <div class="hidden print:flex justify-between items-start mt-12 text-sm text-slate-800">
        <div>
            <p class="text-xs text-slate-500">Document generated automatically by FlyIndonesia Business System.</p>
            <p class="text-xs text-slate-500 mt-0.5">Date generated: {{ date('d F Y, H:i:s') }}</p>
        </div>
        <div class="text-center w-56 space-y-16">
            <div>
                <p>Jakarta, {{ date('d F Y') }}</p>
                <p class="font-semibold text-slate-950 mt-1">Manager Operasional,</p>
            </div>
            <div class="border-b border-slate-650 w-full"></div>
            <p class="font-bold text-slate-950 uppercase">{{ Auth::user()->name }}</p>
        </div>
    </div>
</div>
@endsection
