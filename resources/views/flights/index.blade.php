@extends('layouts.app')

@section('title', 'Search Flights')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <!-- Hero Text -->
    <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-black text-white leading-tight font-display">
            Find Your Next <span class="bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">Adventure</span>
        </h1>
        <p class="text-slate-400 mt-3 text-lg">Book tickets, select your seats, and fly across Indonesia with real-time updates</p>
    </div>

    <!-- Search Card -->
    <div class="glass-card rounded-3xl p-8 glow-cyan relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-cyan-500 to-blue-500"></div>

        <form action="{{ route('flights.search') }}" method="GET" class="space-y-6">
            
            <!-- Origin & Destination -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Origin Autocomplete -->
                <div class="relative">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">From (Origin)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="plane-takeoff" class="h-5 w-5 text-slate-500"></i>
                        </span>
                        <input type="text" id="origin-input" placeholder="City or IATA Code..." required autocomplete="off"
                            class="block w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition text-sm">
                        <input type="hidden" name="from" id="origin-id">
                    </div>
                    <!-- Dropdown -->
                    <div id="origin-dropdown" class="absolute left-0 w-full mt-2 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl py-1 hidden z-50 max-h-60 overflow-y-auto"></div>
                </div>

                <!-- Destination Autocomplete -->
                <div class="relative">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">To (Destination)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="plane-landing" class="h-5 w-5 text-slate-500"></i>
                        </span>
                        <input type="text" id="dest-input" placeholder="City or IATA Code..." required autocomplete="off"
                            class="block w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition text-sm">
                        <input type="hidden" name="to" id="dest-id">
                    </div>
                    <!-- Dropdown -->
                    <div id="dest-dropdown" class="absolute left-0 w-full mt-2 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl py-1 hidden z-50 max-h-60 overflow-y-auto"></div>
                </div>
            </div>

            <!-- Date and Cabin Class -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-semibold text-slate-300 mb-2">Departure Date</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="calendar" class="h-5 w-5 text-slate-500"></i>
                        </span>
                        <input type="date" name="date" id="date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                            class="block w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition text-sm">
                    </div>
                </div>

                <!-- Class -->
                <div>
                    <label for="class" class="block text-sm font-semibold text-slate-300 mb-2">Cabin Class</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="armchair" class="h-5 w-5 text-slate-500"></i>
                        </span>
                        <select name="class" id="class" required
                            class="block w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition text-sm appearance-none">
                            <option value="economy">Economy Class (Base Price)</option>
                            <option value="business">Business Class (+50%)</option>
                            <option value="first">First Class (+100%)</option>
                        </select>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="h-4 w-4 text-slate-500"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-4">
                <button type="submit"
                    class="w-full flex justify-center py-4 px-4 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 border border-transparent rounded-xl text-sm font-bold text-white transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 shadow-xl shadow-cyan-950/20 glow-cyan">
                    <i data-lucide="search" class="w-5 h-5 mr-2"></i> Search Flights
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Autocomplete for Source and Destination inputs
        setupAutocomplete('origin-input', 'origin-id', 'origin-dropdown');
        setupAutocomplete('dest-input', 'dest-id', 'dest-dropdown');
    });

    function setupAutocomplete(inputId, hiddenId, dropdownId) {
        const input = document.getElementById(inputId);
        const hidden = document.getElementById(hiddenId);
        const dropdown = document.getElementById(dropdownId);

        let timeout = null;

        input.addEventListener('input', () => {
            clearTimeout(timeout);
            const val = input.value.trim();
            if (val.length < 2) {
                dropdown.innerHTML = '';
                dropdown.classList.add('hidden');
                hidden.value = '';
                return;
            }

            // Debounce requests
            timeout = setTimeout(() => {
                fetch(`/api/airports/search?q=${encodeURIComponent(val)}`)
                    .then(res => res.json())
                    .then(data => {
                        dropdown.innerHTML = '';
                        if (data.length === 0) {
                            const item = document.createElement('div');
                            item.className = 'px-4 py-3 text-sm text-slate-500 text-center';
                            item.innerText = 'No airports found';
                            dropdown.appendChild(item);
                        } else {
                            data.forEach(airport => {
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'w-full text-left px-4 py-3 hover:bg-slate-800 transition flex justify-between items-center text-sm border-b border-slate-800/50 last:border-0';
                                item.innerHTML = `
                                    <div>
                                        <div class="font-bold text-white">${airport.city} (${airport.iata_code})</div>
                                        <div class="text-xs text-slate-400">${airport.name}</div>
                                    </div>
                                    <div class="text-xs font-semibold text-slate-500 uppercase px-2 py-1 bg-slate-950 border border-slate-800 rounded-lg">${airport.country}</div>
                                `;
                                
                                item.addEventListener('click', () => {
                                    input.value = `${airport.city} (${airport.iata_code})`;
                                    hidden.value = airport.id;
                                    dropdown.classList.add('hidden');
                                });
                                dropdown.appendChild(item);
                            });
                        }
                        dropdown.classList.remove('hidden');
                    });
            }, 300);
        });

        // Hide dropdown on clicking outside
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }
</script>
@endsection
