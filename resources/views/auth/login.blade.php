@extends('layouts.app')

@section('title', 'Log In')

@section('content')
<div class="max-w-md mx-auto my-10">
    <div class="glass-card rounded-3xl p-8 glow-cyan relative overflow-hidden">
        
        <!-- Glowing card header decoration -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-500 to-blue-500"></div>

        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-white tracking-tight">Welcome Back</h2>
            <p class="text-sm text-slate-400 mt-2">Log in to search flights and manage your bookings</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-300 mb-2">Email Address</label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="mail" class="h-5 h-5 text-slate-500"></i>
                    </div>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        class="block w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition text-sm"
                        placeholder="you@example.com">
                </div>
                @error('email')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-300 mb-2">Password</label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="lock" class="h-5 h-5 text-slate-500"></i>
                    </div>
                    <input type="password" name="password" id="password" required
                        class="block w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition text-sm"
                        placeholder="••••••••">
                </div>
                @error('password')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 bg-slate-900 border-slate-800 rounded text-cyan-500 focus:ring-cyan-500/20 focus:ring-offset-slate-900 transition">
                    <label for="remember" class="ml-2 block text-sm text-slate-300 font-medium">Remember me</label>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                <div class="flex items-center justify-between mb-3">
                    <label for="captcha_answer" class="text-sm font-semibold text-slate-300">Captcha</label>
                    <span class="text-sm font-mono text-cyan-400">{{ $captchaQuestion }}</span>
                </div>
                <input type="text" name="captcha_answer" id="captcha_answer" value="{{ old('captcha_answer') }}" required
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition text-sm"
                    placeholder="Enter the result">
                @error('captcha_answer')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            @if(env('RECAPTCHA_ENABLED', false))
                <div class="flex justify-center my-4">
                    <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" data-theme="dark"></div>
                </div>
                @error('g-recaptcha')
                    <p class="text-xs text-center text-red-400 font-medium">{{ $message }}</p>
                @enderror
            @endif

            <!-- Submit Button -->
            <div>
                <button type="submit"
                    class="w-full flex justify-center py-3.5 px-4 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 border border-transparent rounded-xl text-sm font-semibold text-white transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 shadow-lg shadow-cyan-950/20 glow-cyan">
                    Log In
                </button>
            </div>
        </form>

        <div class="mt-8 text-center border-t border-slate-900 pt-6">
            <p class="text-sm text-slate-400">
                New to FlyIndonesia? 
                <a href="{{ route('register') }}" class="font-semibold text-cyan-400 hover:text-cyan-300 transition">Create an account</a>
            </p>
        </div>
    </div>
</div>
@endsection
