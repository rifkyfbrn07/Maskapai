@extends('layouts.app')

@section('title', 'Verify Email')

@section('content')
<div class="max-w-md mx-auto my-10">
    <div class="glass-card rounded-3xl p-8 glow-cyan relative overflow-hidden">
        
        <!-- Glowing card header decoration -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-500 to-blue-500"></div>

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 rounded-2xl mb-4">
                <i data-lucide="shield-check" class="w-8 h-8"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-white tracking-tight">Verify Your Account</h2>
            <p class="text-sm text-slate-400 mt-2">We have sent a 6-digit verification code (OTP) to your registered email <strong class="text-slate-200">{{ $email }}</strong></p>
        </div>

        <form action="{{ route('verification.verify') }}" method="POST" class="space-y-6">
            @csrf

            <!-- OTP Code -->
            <div>
                <label for="otp" class="block text-sm font-semibold text-slate-300 text-center mb-4">Enter 6-Digit Code</label>
                <input type="text" name="otp" id="otp" required maxlength="6" autofocus
                    class="block w-full tracking-[1.5em] text-center font-mono font-bold text-2xl py-4 bg-slate-900 border border-slate-800 rounded-2xl text-cyan-400 placeholder-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition"
                    placeholder="000000">
                @error('otp')
                    <p class="mt-2 text-center text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                    class="w-full flex justify-center py-3.5 px-4 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 border border-transparent rounded-xl text-sm font-semibold text-white transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 shadow-lg shadow-cyan-950/20 glow-cyan">
                    Verify Code
                </button>
            </div>
        </form>

        @if (config('app.env') === 'local' || config('app.debug'))
            <div class="mt-6 p-4 bg-slate-950/50 border border-cyan-500/20 rounded-2xl text-center">
                <span class="text-xs text-slate-400 block mb-2 uppercase tracking-wider font-semibold">Development Helper</span>
                <p class="text-sm text-slate-300">
                    OTP Code: <strong class="text-cyan-400 font-mono text-base">{{ auth()->user()->verification_code }}</strong>
                </p>
                <button type="button" onclick="bypassOtp('{{ auth()->user()->verification_code }}')"
                    class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 text-cyan-400 text-xs font-semibold rounded-lg transition cursor-pointer">
                    <i data-lucide="zap" class="w-3.5 h-3.5"></i> Auto Fill & Verify
                </button>
            </div>
            <script>
                function bypassOtp(code) {
                    const otpInput = document.getElementById('otp');
                    otpInput.value = code;
                    setTimeout(() => {
                        otpInput.form.submit();
                    }, 100);
                }
            </script>
        @endif

        <div class="mt-8 text-center border-t border-slate-900 pt-6">
            <p class="text-sm text-slate-400">
                Didn't receive the code? 
                <form action="{{ route('verification.resend') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="font-semibold text-cyan-400 hover:text-cyan-300 transition focus:outline-none">
                        Resend OTP
                    </button>
                </form>
            </p>
        </div>
    </div>
</div>
@endsection
