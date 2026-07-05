@extends('layouts.app')

@section('title', 'Passenger details & Seating')

@section('content')
<div class="py-4">
    <div class="mb-8 pb-6 border-b border-slate-900">
        <h1 class="text-3xl font-extrabold text-white font-display">Passenger details & Seating</h1>
        <p class="text-sm text-slate-400 mt-1">Flight {{ $flight->flight_number }} &bull; {{ $flight->departureAirport->city }} &rarr; {{ $flight->arrivalAirport->city }}</p>
    </div>

    <!-- Flight Summary Card -->
    <div class="glass-card rounded-2xl p-6 mb-8 border border-slate-900 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-white/5 border border-slate-800 rounded-xl flex items-center justify-center p-2">
                @if($flight->airplane->airline->logo)
                    <img src="{{ $flight->airplane->airline->logo }}" alt="" class="max-w-full max-h-full object-contain">
                @endif
            </div>
            <div>
                <h3 class="font-bold text-white">{{ $flight->airplane->airline->name }} &bull; <span class="text-cyan-400 font-semibold capitalize">{{ $seatClass }} Class</span></h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ date('H:i', strtotime($flight->departure_time)) }} ({{ $flight->departureAirport->iata_code }}) &rarr; {{ date('H:i', strtotime($flight->arrival_time)) }} ({{ $flight->arrivalAirport->iata_code }})</p>
            </div>
        </div>
        <div class="text-left md:text-right">
            <span class="text-xs text-slate-400 block uppercase font-semibold">Price per passenger</span>
            <span class="text-xl font-extrabold text-white">IDR {{ number_format($pricePerPassenger, 0, ',', '.') }}</span>
        </div>
    </div>

    <form action="{{ route('bookings.store') }}" method="POST" id="booking-form">
        @csrf
        <input type="hidden" name="flight_id" value="{{ $flight->id }}">
        <input type="hidden" name="class" value="{{ $seatClass }}">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Side: Manifest forms -->
            <div class="lg:col-span-7 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i data-lucide="users" class="w-5 h-5 mr-2 text-cyan-400"></i> Passenger Manifest
                    </h2>
                    <div class="flex items-center space-x-2">
                        <button type="button" id="remove-passenger-btn" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs font-semibold text-red-400 hover:text-red-300 rounded-lg transition">
                            - Remove Pax
                        </button>
                        <button type="button" id="add-passenger-btn" class="px-3 py-1.5 bg-cyan-500 hover:bg-cyan-400 text-xs font-bold text-white rounded-lg transition shadow-lg shadow-cyan-950/20">
                            + Add Pax
                        </button>
                    </div>
                </div>

                <!-- Passenger Forms Container -->
                <div id="passengers-container" class="space-y-6">
                    <!-- Passenger 1 Template (First is always required) -->
                    <div class="passenger-card glass-card rounded-2xl p-6 relative border border-slate-900 transition duration-150" data-index="0">
                        <div class="absolute top-0 left-0 w-1 h-full bg-cyan-500 rounded-l-2xl"></div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-white text-sm uppercase tracking-wider flex items-center">
                                <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-xs mr-2 font-black">1</span>
                                Passenger #1
                            </h3>
                            <span class="text-xs text-slate-500">Active selection for Seat Map</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Name -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Full Name (Matching NIK/Passport)</label>
                                <input type="text" name="passengers[0][name]" required
                                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition">
                            </div>
                            
                            <!-- Passport / NIK -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1.5">NIK / Passport Number</label>
                                <input type="text" name="passengers[0][passport_number]" required
                                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                                    placeholder="e.g. 3275080000000000">
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Gender</label>
                                <select name="passengers[0][gender]" required
                                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>

                            <!-- Birth Date -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Date of Birth</label>
                                <input type="date" name="passengers[0][birth_date]" required max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition">
                            </div>

                            <!-- Seat Number -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Selected Seat</label>
                                <div class="flex space-x-2">
                                    <input type="text" name="passengers[0][seat_number]" id="pax-seat-0" readonly required
                                        class="block w-full px-4 py-2.5 bg-slate-950 border border-slate-900 rounded-xl text-cyan-400 font-mono font-bold text-sm focus:outline-none cursor-pointer"
                                        placeholder="Click select seat button or choose on map below..."
                                        onclick="selectActivePax(0)">
                                    <button type="button" onclick="selectActivePax(0)" id="pax-btn-0"
                                        class="px-4 py-2.5 bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-xs font-bold rounded-xl hover:bg-cyan-500/20 transition flex-shrink-0 flex items-center">
                                        <i data-lucide="armchair" class="w-4 h-4 mr-1"></i> Select Seat
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Interactive Seat Picker -->
            <div class="lg:col-span-5">
                <div class="glass-card rounded-2xl p-6 sticky top-24 border border-slate-900">
                    <h2 class="text-xl font-bold text-white mb-2 flex items-center">
                        <i data-lucide="grid" class="w-5 h-5 mr-2 text-cyan-400"></i> Cabin Seating Map
                    </h2>
                    <p class="text-xs text-slate-400 mb-6">Choose a seat for the passenger highlighted in yellow</p>

                    <!-- Legend -->
                    <div class="grid grid-cols-3 gap-2 text-xs font-medium text-slate-400 mb-6 border-b border-slate-900 pb-4">
                        <div class="flex items-center">
                            <span class="w-3.5 h-3.5 bg-slate-800 border border-slate-700 rounded mr-2"></span> Available
                        </div>
                        <div class="flex items-center">
                            <span class="w-3.5 h-3.5 bg-red-500/20 border border-red-500/30 text-red-500 rounded mr-2"></span> Occupied
                        </div>
                        <div class="flex items-center">
                            <span class="w-3.5 h-3.5 bg-cyan-500 border border-cyan-400 rounded mr-2"></span> Selected
                        </div>
                    </div>

                    <!-- Seating Layout grid -->
                    <div class="max-h-[50vh] overflow-y-auto bg-slate-950/40 rounded-xl p-4 border border-slate-900">
                        
                        <!-- Header: Column Labels -->
                        <div class="grid grid-cols-7 gap-1 text-center font-mono font-bold text-slate-500 text-xs mb-3">
                            <div>A</div>
                            <div>B</div>
                            <div>C</div>
                            <div class="text-[10px] text-slate-700 uppercase font-sans">Aisle</div>
                            <div>D</div>
                            <div>E</div>
                            <div>F</div>
                        </div>

                        <!-- Rows -->
                        <div class="space-y-2">
                            @php
                                $groupedSeats = $allSeats->groupBy(function($seat) {
                                    return preg_replace('/[^0-9]/', '', $seat->seat_number);
                                });
                            @endphp

                            @foreach($groupedSeats as $rowNumber => $rowSeats)
                                <div class="grid grid-cols-7 gap-1 items-center">
                                    @php
                                        // Arrange row seats into columns A, B, C, then spacer, then D, E, F
                                        $columns = ['A', 'B', 'C', 'spacer', 'D', 'E', 'F'];
                                        $seatsByLetter = $rowSeats->keyBy(function($seat) {
                                            return preg_replace('/[0-9]/', '', $seat->seat_number);
                                        });
                                    @endphp

                                    @foreach($columns as $col)
                                        @if($col === 'spacer')
                                            <div class="text-center font-bold text-[10px] text-slate-700 font-mono">{{ $rowNumber }}</div>
                                        @else
                                            @php
                                                $seat = $seatsByLetter->get($col);
                                            @endphp

                                            @if($seat)
                                                @php
                                                    $isOccupied = in_array($seat->seat_number, $occupiedSeats);
                                                @endphp
                                                <button type="button" 
                                                    id="seat-btn-{{ $seat->seat_number }}"
                                                    data-seat="{{ $seat->seat_number }}"
                                                    class="seat-element py-1.5 text-xs font-mono font-bold rounded-lg border transition text-center
                                                        {{ $isOccupied 
                                                            ? 'bg-red-500/10 border-red-500/30 text-red-500 cursor-not-allowed' 
                                                            : 'bg-slate-900 border-slate-800 hover:border-cyan-500/50 text-slate-300' }}"
                                                    {{ $isOccupied ? 'disabled' : '' }}
                                                    onclick="onSeatClick('{{ $seat->seat_number }}')">
                                                    {{ $seat->seat_number }}
                                                </button>
                                            @else
                                                <div class="py-1.5"></div>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Total Price Calculation -->
                    <div class="mt-6 border-t border-slate-900 pt-6 space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400">Total Pax</span>
                            <span class="text-white font-bold" id="summary-pax">1 Passenger</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-slate-900 pb-3">
                            <span class="text-slate-400">Price per Pax</span>
                            <span class="text-white font-semibold">IDR {{ number_format($pricePerPassenger, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-300 font-semibold">Total Price</span>
                            <span class="text-xl font-black text-cyan-400" id="summary-total">IDR {{ number_format($pricePerPassenger, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Submit Booking -->
                    <button type="submit" form="booking-form"
                        class="w-full mt-6 py-4 px-4 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-sm font-bold text-white rounded-xl shadow-lg shadow-cyan-950/20 glow-cyan transition">
                        Confirm Bookings
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let activePaxIndex = 0;
    let totalPassengers = 1;
    const basePrice = {{ $pricePerPassenger }};

    // Store chosen seats: { passengerIndex: seatNumber }
    let chosenSeats = {};

    document.addEventListener('DOMContentLoaded', () => {
        setupPassengerFormLogic();
        selectActivePax(0); // Set passenger 1 as default active selection
    });

    function setupPassengerFormLogic() {
        const container = document.getElementById('passengers-container');
        const addBtn = document.getElementById('add-passenger-btn');
        const removeBtn = document.getElementById('remove-passenger-btn');

        addBtn.addEventListener('click', () => {
            if (totalPassengers >= 10) {
                alert('Maximum booking count is 10 passengers.');
                return;
            }
            
            const index = totalPassengers;
            totalPassengers++;
            
            const card = document.createElement('div');
            card.className = 'passenger-card glass-card rounded-2xl p-6 relative border border-slate-900 transition duration-150';
            card.setAttribute('data-index', index);
            card.id = `passenger-card-${index}`;
            card.innerHTML = `
                <div class="absolute top-0 left-0 w-1 h-full bg-slate-800 rounded-l-2xl" id="passenger-decor-${index}"></div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-white text-sm uppercase tracking-wider flex items-center">
                        <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center text-xs mr-2 font-black" id="passenger-badge-${index}">${index + 1}</span>
                        Passenger #${index + 1}
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Full Name</label>
                        <input type="text" name="passengers[${index}][name]" required
                            class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">NIK / Passport Number</label>
                        <input type="text" name="passengers[${index}][passport_number]" required
                            class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                            placeholder="e.g. 3275080000000000">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Gender</label>
                        <select name="passengers[${index}][gender]" required
                            class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Date of Birth</label>
                        <input type="date" name="passengers[${index}][birth_date]" required max="${new Date().toISOString().split('T')[0]}"
                            class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Selected Seat</label>
                        <div class="flex space-x-2">
                            <input type="text" name="passengers[${index}][seat_number]" id="pax-seat-${index}" readonly required
                                class="block w-full px-4 py-2.5 bg-slate-950 border border-slate-900 rounded-xl text-cyan-400 font-mono font-bold text-sm focus:outline-none cursor-pointer"
                                placeholder="Click select seat button or choose on map below..."
                                onclick="selectActivePax(${index})">
                            <button type="button" onclick="selectActivePax(${index})" id="pax-btn-${index}"
                                class="px-4 py-2.5 bg-slate-800 border border-slate-800 text-slate-400 text-xs font-bold rounded-xl hover:bg-slate-700/50 transition flex-shrink-0 flex items-center">
                                <i data-lucide="armchair" class="w-4 h-4 mr-1"></i> Select Seat
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(card);
            lucide.createIcons(); // re-trigger icon parser
            selectActivePax(index);
            updateSummary();
        });

        removeBtn.addEventListener('click', () => {
            if (totalPassengers <= 1) {
                return;
            }
            
            const index = totalPassengers - 1;
            totalPassengers--;
            
            // Clear chosen seat for this passenger if any
            if (chosenSeats[index]) {
                const seatNum = chosenSeats[index];
                deleteSeatSelection(seatNum);
                delete chosenSeats[index];
            }

            const card = document.getElementById(`passenger-card-${index}`);
            if (card) {
                card.remove();
            }

            if (activePaxIndex === index) {
                selectActivePax(index - 1);
            }
            
            updateSummary();
        });
    }

    function selectActivePax(index) {
        activePaxIndex = index;

        // Reset styling for all passenger cards
        const cards = document.querySelectorAll('.passenger-card');
        cards.forEach((card, idx) => {
            const cardIdx = parseInt(card.getAttribute('data-index'));
            const decor = card.querySelector(`[id^="passenger-decor-"]`) || card.querySelector('.absolute');
            const badge = card.querySelector(`[id^="passenger-badge-"]`) || card.querySelector('.w-6');
            const btn = document.getElementById(`pax-btn-${cardIdx}`);

            if (cardIdx === activePaxIndex) {
                // Set Active card visuals
                card.classList.add('border-cyan-500/30', 'scale-[1.01]');
                card.classList.remove('border-slate-900');
                if (decor) decor.className = "absolute top-0 left-0 w-1 h-full bg-cyan-500 rounded-l-2xl";
                if (badge) badge.className = "w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-xs mr-2 font-black";
                if (btn) btn.className = "px-4 py-2.5 bg-cyan-500 border border-cyan-400 text-white text-xs font-bold rounded-xl hover:bg-cyan-400 transition flex-shrink-0 flex items-center";
            } else {
                // Set Inactive card visuals
                card.classList.remove('border-cyan-500/30', 'scale-[1.01]');
                card.classList.add('border-slate-900');
                if (decor) decor.className = "absolute top-0 left-0 w-1 h-full bg-slate-800 rounded-l-2xl";
                if (badge) badge.className = "w-6 h-6 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center text-xs mr-2 font-black";
                if (btn) btn.className = "px-4 py-2.5 bg-slate-800 border border-slate-800 text-slate-400 text-xs font-bold rounded-xl hover:bg-slate-700/50 transition flex-shrink-0 flex items-center";
            }
        });
    }

    function onSeatClick(seatNumber) {
        // If seat is occupied, ignore
        const seatBtn = document.getElementById(`seat-btn-${seatNumber}`);
        if (seatBtn.disabled || seatBtn.classList.contains('bg-red-500/10')) {
            return;
        }

        // Check if seat is already selected by another passenger
        for (let idx in chosenSeats) {
            if (parseInt(idx) !== activePaxIndex && chosenSeats[idx] === seatNumber) {
                alert(`Seat ${seatNumber} is already selected by Passenger #${parseInt(idx) + 1}.`);
                return;
            }
        }

        // Release old seat selected by this active passenger
        if (chosenSeats[activePaxIndex]) {
            const oldSeat = chosenSeats[activePaxIndex];
            deleteSeatSelection(oldSeat);
        }

        // Select new seat
        chosenSeats[activePaxIndex] = seatNumber;
        document.getElementById(`pax-seat-${activePaxIndex}`).value = seatNumber;

        // Visual update on seat map
        seatBtn.className = "seat-element py-1.5 text-xs font-mono font-bold rounded-lg border text-center bg-cyan-500 border-cyan-400 text-white";

        // Auto select next passenger if available
        if (activePaxIndex < totalPassengers - 1) {
            setTimeout(() => {
                selectActivePax(activePaxIndex + 1);
            }, 300);
        }
    }

    function deleteSeatSelection(seatNumber) {
        const seatBtn = document.getElementById(`seat-btn-${seatNumber}`);
        if (seatBtn) {
            // Restore default styles
            seatBtn.className = "seat-element py-1.5 text-xs font-mono font-bold rounded-lg border text-center bg-slate-900 border-slate-800 hover:border-cyan-500/50 text-slate-300";
        }
    }

    function updateSummary() {
        const summaryPax = document.getElementById('summary-pax');
        const summaryTotal = document.getElementById('summary-total');

        summaryPax.innerText = `${totalPassengers} Passenger${totalPassengers > 1 ? 's' : ''}`;
        const totalAmount = basePrice * totalPassengers;
        summaryTotal.innerText = `IDR ${totalAmount.toLocaleString('id-ID')}`;
    }
</script>
@endsection
