@extends('layouts.app')

@section('title', 'Sign Up')

@section('content')
<div class="max-w-md mx-auto my-6">
    <div class="glass-card rounded-3xl p-8 glow-cyan relative overflow-hidden">
        
        <!-- Glowing card header decoration -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-500 to-blue-500"></div>

        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-white tracking-tight">Create Account</h2>
            <p class="text-sm text-slate-400 mt-2">Sign up to find the best flight routes and travel deals</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-300 mb-2">Full Name</label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="user" class="h-5 h-5 text-slate-500"></i>
                    </div>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                        class="block w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition text-sm"
                        placeholder="John Doe">
                </div>
                @error('name')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-300 mb-2">Email Address</label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="mail" class="h-5 h-5 text-slate-500"></i>
                    </div>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
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
                        placeholder="Min 6 characters">
                </div>
                @error('password')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-300 mb-2">Confirm Password</label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="lock-keyhole" class="h-5 h-5 text-slate-500"></i>
                    </div>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="block w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition text-sm"
                        placeholder="Re-type password">
                </div>
            </div>

            <!-- Google reCAPTCHA -->
            @if(env('RECAPTCHA_ENABLED', false))
                <div class="flex justify-center my-3">
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
                    Create Account
                </button>
            </div>
        </form>

        <div class="mt-8 text-center border-t border-slate-900 pt-5">
            <p class="text-sm text-slate-400">
                Already have an account? 
                <a href="{{ route('login') }}" class="font-semibold text-cyan-400 hover:text-cyan-300 transition">Log in here</a>
            </p>
        </div>
    </div>
</div>
@endsection
