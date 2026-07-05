@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
<div class="py-4">
    <!-- Back to search and summary header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-6 border-b border-slate-900">
        <div>
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-cyan-400 hover:text-cyan-300 transition mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to search
            </a>
            <h1 class="text-3xl font-extrabold text-white font-display">
                Flights from {{ $fromAirport->city }} ({{ $fromAirport->iata_code }}) to {{ $toAirport->city }} ({{ $toAirport->iata_code }})
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                {{ date('l, d F Y', strtotime($date)) }} &bull; <span class="capitalize text-cyan-300 font-semibold">{{ $seatClass }} Class</span>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
            <div class="glass-card rounded-2xl p-6 sticky top-24">
                <h2 class="text-lg font-bold text-white mb-6 flex items-center">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4 mr-2 text-cyan-400"></i> Filters
                </h2>

                <form action="{{ route('flights.search') }}" method="GET" class="space-y-6">
                    <input type="hidden" name="from" value="{{ request('from') }}">
                    <input type="hidden" name="to" value="{{ request('to') }}">
                    <input type="hidden" name="date" value="{{ request('date') }}">
                    <input type="hidden" name="class" value="{{ request('class') }}">

                    <!-- Sort -->
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-400 tracking-wider mb-2">Sort By</label>
                        <select name="sort" onchange="this.form.submit()"
                            class="block w-full py-2 px-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500">
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="time_asc" {{ request('sort') === 'time_asc' ? 'selected' : '' }}>Departure: Earliest</option>
                            <option value="time_desc" {{ request('sort') === 'time_desc' ? 'selected' : '' }}>Departure: Latest</option>
                        </select>
                    </div>

                    <!-- Airline -->
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-400 tracking-wider mb-2">Airline</label>
                        <select name="airline" onchange="this.form.submit()"
                            class="block w-full py-2 px-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500">
                            <option value="">All Airlines</option>
                            @foreach($airlines as $airline)
                                <option value="{{ $airline->id }}" {{ request('airline') == $airline->id ? 'selected' : '' }}>{{ $airline->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Time Period -->
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-400 tracking-wider mb-2">Departure Time</label>
                        <div class="space-y-2 text-sm text-slate-300">
                            <label class="flex items-center">
                                <input type="radio" name="time_period" value="" {{ !request('time_period') ? 'checked' : '' }} onchange="this.form.submit()"
                                    class="bg-slate-950 border-slate-800 text-cyan-500 focus:ring-cyan-500/20 mr-2">
                                All Times
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="time_period" value="morning" {{ request('time_period') === 'morning' ? 'checked' : '' }} onchange="this.form.submit()"
                                    class="bg-slate-950 border-slate-800 text-cyan-500 focus:ring-cyan-500/20 mr-2">
                                Morning (06:00 - 12:00)
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="time_period" value="afternoon" {{ request('time_period') === 'afternoon' ? 'checked' : '' }} onchange="this.form.submit()"
                                    class="bg-slate-950 border-slate-800 text-cyan-500 focus:ring-cyan-500/20 mr-2">
                                Afternoon (12:00 - 18:00)
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="time_period" value="evening" {{ request('time_period') === 'evening' ? 'checked' : '' }} onchange="this.form.submit()"
                                    class="bg-slate-950 border-slate-800 text-cyan-500 focus:ring-cyan-500/20 mr-2">
                                Evening (18:00 - 24:00)
                            </label>
                        </div>
                    </div>

                    <!-- Price Filter inputs -->
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-400 tracking-wider mb-2">Price Range (IDR)</label>
                        <div class="flex items-center space-x-2">
                            <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}"
                                class="w-full py-2 px-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-cyan-500">
                            <span class="text-slate-600 text-xs">-</span>
                            <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}"
                                class="w-full py-2 px-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-cyan-500">
                        </div>
                        <button type="submit" class="w-full mt-3 py-2 px-4 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs text-cyan-400 hover:text-cyan-300 font-semibold rounded-xl transition">
                            Apply Price Filter
                        </button>
                    </div>

                    @if(request()->anyFilled(['sort', 'airline', 'time_period', 'price_min', 'price_max']))
                        <a href="{{ route('flights.search', ['from' => request('from'), 'to' => request('to'), 'date' => request('date'), 'class' => request('class')]) }}" 
                           class="block text-center text-xs text-slate-500 hover:text-slate-400 transition font-medium border-t border-slate-800 pt-4 mt-2">
                            Clear all filters
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Flights List -->
        <div class="lg:col-span-3 space-y-6">
            @if($flights->isEmpty())
                <div class="glass-card rounded-3xl p-12 text-center text-slate-400 max-w-xl mx-auto border border-slate-800">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-900 border border-slate-800 text-slate-500 rounded-2xl mb-4">
                        <i data-lucide="plane" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">No Flights Available</h3>
                    <p class="text-sm text-slate-400 mb-6">There are no flight schedules matching your current filters or search criteria. Try changing your departure date or removing filters.</p>
                    <a href="{{ route('home') }}" class="inline-flex items-center py-2.5 px-6 bg-cyan-500 hover:bg-cyan-400 text-white font-semibold rounded-full transition shadow-lg shadow-cyan-950/20">
                        Change Search
                    </a>
                </div>
            @else
                @foreach($flights as $flight)
                    <!-- Flight Card -->
                    <div class="glass-card rounded-2xl p-6 hover:border-slate-700 hover:scale-[1.01] transition duration-150 relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
                        
                        <!-- Flight Basic Info -->
                        <div class="flex items-center space-x-4">
                            <!-- Logo -->
                            <div class="w-16 h-16 bg-white/5 border border-slate-800 rounded-2xl flex items-center justify-center p-2 flex-shrink-0">
                                @if($flight->airplane->airline->logo)
                                    <img src="{{ $flight->airplane->airline->logo }}" alt="{{ $flight->airplane->airline->name }}" class="max-w-full max-h-full object-contain">
                                @else
                                    <i data-lucide="plane" class="w-6 h-6 text-slate-500"></i>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-white leading-tight">{{ $flight->airplane->airline->name }}</h3>
                                <p class="text-xs text-slate-500 font-mono mt-1 uppercase">{{ $flight->flight_number }} &bull; {{ $flight->airplane->model }}</p>
                            </div>
                        </div>

                        <!-- Routing timeline -->
                        <div class="flex items-center flex-grow md:justify-center px-4">
                            <!-- Departure -->
                            <div class="text-right w-24">
                                <span class="block font-bold text-lg text-white leading-tight">{{ date('H:i', strtotime($flight->departure_time)) }}</span>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mt-0.5">{{ $flight->departureAirport->iata_code }}</span>
                                <span class="block text-[10px] text-slate-500 truncate mt-0.5">{{ $flight->departureAirport->city }}</span>
                            </div>

                            <!-- Connection Line -->
                            <div class="flex flex-col items-center flex-grow max-w-[120px] px-4 relative">
                                <span class="text-[10px] text-slate-500 font-semibold mb-1">
                                    {{ gmdate('H\h i\m', strtotime($flight->arrival_time) - strtotime($flight->departure_time)) }}
                                </span>
                                <div class="w-full h-0.5 bg-slate-800 relative flex items-center">
                                    <div class="absolute w-2 h-2 rounded-full bg-slate-700 left-0"></div>
                                    <div class="absolute w-2 h-2 rounded-full bg-cyan-500 right-0 animate-ping"></div>
                                    <div class="absolute w-2 h-2 rounded-full bg-cyan-500 right-0"></div>
                                </div>
                                <span class="text-[10px] font-bold text-cyan-400 mt-1 uppercase tracking-widest">Direct</span>
                            </div>

                            <!-- Arrival -->
                            <div class="text-left w-24">
                                <span class="block font-bold text-lg text-white leading-tight">{{ date('H:i', strtotime($flight->arrival_time)) }}</span>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mt-0.5">{{ $flight->arrivalAirport->iata_code }}</span>
                                <span class="block text-[10px] text-slate-500 truncate mt-0.5">{{ $flight->arrivalAirport->city }}</span>
                            </div>
                        </div>

                        <!-- Price and Checkout -->
                        <div class="border-t border-slate-900 md:border-t-0 pt-4 md:pt-0 flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-4 flex-shrink-0 md:pl-6 md:border-l md:border-slate-800">
                            <div>
                                <span class="block text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Price per pax</span>
                                <span class="block text-2xl font-black text-cyan-400 leading-none mt-1">IDR {{ number_format($flight->display_price, 0, ',', '.') }}</span>
                                <span class="block text-[10px] text-slate-500 font-medium mt-1">
                                    @if($flight->available_seats <= 0)
                                        <span class="text-red-500 font-bold">SOLD OUT</span>
                                    @elseif($flight->available_seats <= 15)
                                        <span class="text-amber-500 font-bold">Only {{ $flight->available_seats }} seats left</span>
                                    @else
                                        {{ $flight->available_seats }} seats available
                                    @endif
                                </span>
                            </div>

                            @if($flight->available_seats > 0)
                                <a href="{{ route('bookings.new', ['flight' => $flight->id, 'class' => $seatClass]) }}"
                                    class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-sm font-semibold text-white px-6 py-2.5 rounded-full transition shadow-md shadow-cyan-950/20">
                                    Select Flight
                                </a>
                            @else
                                <button disabled class="bg-slate-800 text-sm font-semibold text-slate-600 px-6 py-2.5 rounded-full cursor-not-allowed">
                                    Sold Out
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
