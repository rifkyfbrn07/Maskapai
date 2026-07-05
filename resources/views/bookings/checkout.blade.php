@extends('layouts.app')

@section('title', 'Complete Payment')

@section('content')
<div class="max-w-2xl mx-auto py-6">
    <div class="glass-card rounded-3xl p-8 glow-cyan relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-cyan-500 to-blue-500"></div>

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 rounded-2xl mb-4">
                <i data-lucide="credit-card" class="w-8 h-8"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-white tracking-tight font-display">Secure Checkout</h2>
            <p class="text-sm text-slate-400 mt-2">Complete payment for booking PNR code: <strong class="text-white font-mono font-bold">{{ $booking->booking_code }}</strong></p>
        </div>

        <!-- Order Summary -->
        <div class="bg-slate-950/50 border border-slate-900 rounded-2xl p-6 mb-8 space-y-4">
            <h3 class="font-bold text-white text-sm uppercase tracking-wider border-b border-slate-900 pb-3">Booking Summary</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-slate-400 block">Flight Number</span>
                    <span class="text-white font-bold font-mono">{{ $booking->flight->flight_number }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Route</span>
                    <span class="text-white font-semibold">{{ $booking->flight->departureAirport->city }} &rarr; {{ $booking->flight->arrivalAirport->city }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Passengers count</span>
                    <span class="text-white font-semibold">{{ $booking->passengers->count() }} passenger(s)</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Cabin Class</span>
                    <span class="text-cyan-400 font-bold capitalize">{{ $booking->passengers->first()->seat_number ? 'Economy / Business / First' : 'Class' }}</span>
                </div>
            </div>
            <hr class="border-slate-900">
            <div class="flex justify-between items-center pt-2">
                <span class="text-slate-300 font-semibold">Total Amount</span>
                <span class="text-2xl font-black text-cyan-400">IDR {{ number_format($booking->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($errorMsg)
            <!-- Midtrans Connection Error Alert -->
            <div class="p-4 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-2xl text-sm mb-6 flex items-start">
                <i data-lucide="alert-triangle" class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5"></i>
                <div>
                    <strong class="font-bold block mb-1">Sandbox Gateway Connection Warning:</strong>
                    {{ $errorMsg }}
                    <p class="mt-2 text-xs text-slate-400">This occurs if your sandbox API keys are unconfigured or there is no internet connection. Please use the Local Simulator below to bypass.</p>
                </div>
            </div>
        @endif

        <!-- Payment Actions -->
        <div class="space-y-4">
            @if($snapToken)
                <button type="button" id="pay-button"
                    class="w-full flex justify-center py-4 px-4 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-sm font-bold text-white rounded-xl shadow-lg shadow-cyan-950/20 glow-cyan transition">
                    <i data-lucide="shield-check" class="w-5 h-5 mr-2"></i> Pay Now via Midtrans Snap
                </button>
            @endif

            <a href="{{ route('bookings.history') }}" class="w-full flex justify-center py-3 px-4 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-sm font-semibold text-slate-300 rounded-xl transition">
                Go to My Bookings
            </a>
        </div>

        <!-- Local testing simulator -->
        <div class="mt-10 border-t border-dashed border-slate-900 pt-8">
            <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 text-center">
                <h4 class="text-sm font-bold text-slate-300 uppercase tracking-widest mb-2 flex items-center justify-center">
                    <i data-lucide="cpu" class="w-4 h-4 mr-2 text-purple-400"></i> Offline Testing Simulator
                </h4>
                <p class="text-xs text-slate-500 mb-6">Since this is a school project (UKK), you can trigger immediate mock payment confirmation without needing internet access or active Midtrans credentials.</p>
                
                <form action="{{ route('bookings.check_payment', $booking->id) }}" method="GET">
                    <input type="hidden" name="simulate" value="success">
                    <button type="submit" 
                        class="px-6 py-3 bg-purple-500 hover:bg-purple-400 text-xs font-bold text-white rounded-xl transition shadow-lg shadow-purple-950/20 glow-purple">
                        Simulate Payment Success
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
@if($snapToken)
    <!-- Midtrans Snap Script -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
    <script type="text/javascript">
        var payButton = document.getElementById('pay-button');
        payButton.addEventListener('click', function () {
            // Trigger snap popup
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result){
                    window.location.href = "{{ route('bookings.check_payment', $booking->id) }}?order_id=" + encodeURIComponent(result.order_id || '{{ $orderId }}') + "&transaction_id=" + encodeURIComponent(result.transaction_id || '');
                },
                onPending: function(result){
                    window.location.href = "{{ route('bookings.check_payment', $booking->id) }}?order_id=" + encodeURIComponent(result.order_id || '{{ $orderId }}') + "&transaction_id=" + encodeURIComponent(result.transaction_id || '');
                },
                onError: function(result){
                    alert("Payment failed: " + result.status_message);
                },
                onClose: function(){
                    alert('You closed the payment popup before finishing.');
                }
            });
        });
    </script>
@endif
@endsection
