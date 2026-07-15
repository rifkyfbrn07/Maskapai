<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Flight Booking System') | FlyIndonesia</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google reCAPTCHA -->
    @if(env('RECAPTCHA_ENABLED', false))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4, h5, h6, .font-display {
            font-family: 'Outfit', sans-serif;
        }
        /* Sleek Glassmorphism classes */
        .glass-card {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-nav {
            background: rgba(8, 13, 28, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glow-cyan {
            box-shadow: 0 0 25px -5px rgba(6, 182, 212, 0.4);
        }
        .glow-purple {
            box-shadow: 0 0 25px -5px rgba(168, 85, 247, 0.4);
        }
    </style>
    @yield('styles')
</head>
<body class="flex flex-col min-h-full selection:bg-cyan-500/30 selection:text-cyan-300">

    <!-- Glowing Background blobs -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-[-10]">
        <div class="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] rounded-full bg-cyan-500/10 blur-[120px] animate-pulse" style="animation-duration: 12s;"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-purple-500/10 blur-[100px] animate-pulse" style="animation-duration: 8s;"></div>
    </div>

    <!-- Header / Navigation -->
    <header class="sticky top-0 z-50 glass-nav">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 text-2xl font-black tracking-tight text-white font-display">
                        <span class="bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">Fly</span><span>Indonesia</span>
                    </a>
                </div>
                
                <div class="hidden md:flex space-x-6 text-sm font-medium">
                    <a href="{{ route('home') }}" class="text-slate-300 hover:text-cyan-400 transition">Search Flights</a>
                    @auth
                        @if(auth()->user()->role === 'customer')
                            <a href="{{ route('bookings.history') }}" class="text-slate-300 hover:text-cyan-400 transition">My Bookings</a>
                        @endif
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="text-amber-400 hover:text-amber-300 transition">Admin Panel</a>
                        @elseif(auth()->user()->role === 'staff')
                            <a href="{{ route('staff.dashboard') }}" class="text-indigo-400 hover:text-indigo-300 transition">Staff Panel</a>
                        @elseif(auth()->user()->role === 'manager')
                            <a href="{{ route('manager.dashboard') }}" class="text-emerald-400 hover:text-emerald-300 transition">Manager Dashboard</a>
                        @endif
                    @endauth
                </div>

                <div class="flex items-center space-x-4">
                    @auth
                        <div class="relative group">
                            <button class="flex items-center space-x-2 bg-slate-900 border border-slate-800 hover:border-slate-700 px-4 py-2 rounded-full text-sm font-medium text-slate-200 transition focus:outline-none">
                                <i data-lucide="user" class="w-4 h-4 text-cyan-400"></i>
                                <span class="max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold rounded {{ auth()->user()->role === 'customer' ? 'bg-cyan-500/20 text-cyan-300' : (auth()->user()->role === 'admin' ? 'bg-amber-500/20 text-amber-300' : (auth()->user()->role === 'staff' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-emerald-500/20 text-emerald-300')) }}">
                                    {{ auth()->user()->role }}
                                </span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500"></i>
                            </button>
                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-48 rounded-xl bg-slate-900 border border-slate-800 shadow-xl py-1 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition duration-150 z-50">
                                <div class="px-4 py-2 border-bottom border-slate-800 text-xs text-slate-400 truncate">
                                    {{ auth()->user()->email }}
                                </div>
                                <hr class="border-slate-800">
                                @if(auth()->user()->role === 'customer')
                                    <a href="{{ route('bookings.history') }}" class="flex items-center space-x-2 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-cyan-400 transition">
                                        <i data-lucide="ticket" class="w-4 h-4"></i>
                                        <span>My Tickets</span>
                                    </a>
                                @endif
                                <form action="{{ route('logout') }}" method="POST" class="block">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center space-x-2 px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 transition text-left">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-300 hover:text-cyan-400 transition">Log in</a>
                        <a href="{{ route('register') }}" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-sm font-medium text-white px-5 py-2.5 rounded-full transition shadow-lg shadow-cyan-950/20 glow-cyan">Sign up</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-grow py-10 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Alerts Block -->
        @if(session('success'))
            <div class="mb-6 flex items-center p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-2xl animate-fade-in">
                <i data-lucide="check-circle" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 flex items-center p-4 bg-red-500/10 border border-red-500/30 text-red-400 rounded-2xl animate-fade-in">
                <i data-lucide="alert-circle" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <div class="text-sm font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-8 border-t border-slate-900 bg-slate-950/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-slate-500 text-sm">
            <p>&copy; {{ date('Y') }} FlyIndonesia. Developed for UKK - Uji Kompetensi Keahlian.</p>
            <p class="mt-1 text-xs text-slate-600">Secure booking integrated with Midtrans Sandbox & Google reCAPTCHA</p>
        </div>
    </footer>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    @yield('scripts')
</body>
</html>
