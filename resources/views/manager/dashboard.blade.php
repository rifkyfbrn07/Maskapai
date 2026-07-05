@extends('layouts.app')

@section('title', 'Manager Analytical Dashboard')

@section('content')
<div class="py-4">
    <!-- Header with print actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-6 border-b border-slate-900 print:hidden">
        <div>
            <h1 class="text-3xl font-extrabold text-white font-display">Manager Analytics</h1>
            <p class="text-sm text-slate-400 mt-1">Real-time revenue tracking, route popularity, and class occupancy stats</p>
        </div>
        <div class="flex space-x-3 mt-4 md:mt-0">
            <a href="{{ route('manager.report') }}" class="bg-cyan-500 hover:bg-cyan-400 text-xs font-bold text-white px-5 py-3 rounded-xl transition flex items-center shadow-lg shadow-cyan-950/20 glow-cyan">
                <i data-lucide="file-text" class="w-4 h-4 mr-1.5"></i> Sales Report Generator
            </a>
            <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs font-bold text-slate-300 px-5 py-3 rounded-xl transition flex items-center">
                <i data-lucide="printer" class="w-4 h-4 mr-1.5"></i> Print Dashboard
            </button>
        </div>
    </div>

    <!-- PRINT HEADER -->
    <div class="hidden print:block text-center mb-8 border-b border-slate-900 pb-4">
        <h1 class="text-2xl font-bold text-slate-900">FlyIndonesia Business Report</h1>
        <p class="text-xs text-slate-500">Generated on {{ date('d F Y, H:i') }} &bull; Confirmed Transactions Only</p>
    </div>

    <!-- Core KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Revenue Card -->
        <div class="glass-card rounded-2xl p-6 border border-slate-900 relative overflow-hidden flex items-center justify-between">
            <div class="absolute top-0 left-0 w-full h-1 bg-cyan-500"></div>
            <div>
                <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">Total Sales Revenue</span>
                <span class="text-2xl font-black text-cyan-400 block mt-1">IDR {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center">
                <i data-lucide="banknote" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Bookings Card -->
        <div class="glass-card rounded-2xl p-6 border border-slate-900 relative overflow-hidden flex items-center justify-between">
            <div class="absolute top-0 left-0 w-full h-1 bg-purple-500"></div>
            <div>
                <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">Confirmed Bookings</span>
                <span class="text-2xl font-black text-purple-400 block mt-1">{{ number_format($totalBookings) }} Orders</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center">
                <i data-lucide="shopping-cart" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Passengers Card -->
        <div class="glass-card rounded-2xl p-6 border border-slate-900 relative overflow-hidden flex items-center justify-between">
            <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>
            <div>
                <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">Passengers Transported</span>
                <span class="text-2xl font-black text-emerald-400 block mt-1">{{ number_format($totalPassengers) }} Pax</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        <!-- Sales Trend -->
        <div class="lg:col-span-8 glass-card rounded-2xl p-6 border border-slate-900 flex flex-col justify-between min-h-[350px]">
            <h3 class="font-bold text-white text-sm uppercase tracking-wider mb-4">Weekly Sales Revenue Trend</h3>
            <div class="flex-grow flex items-center justify-center">
                <canvas id="weeklySalesChart" class="max-h-[260px] w-full"></canvas>
            </div>
        </div>

        <!-- Class Occupancy -->
        <div class="lg:col-span-4 glass-card rounded-2xl p-6 border border-slate-900 flex flex-col justify-between min-h-[350px]">
            <h3 class="font-bold text-white text-sm uppercase tracking-wider mb-4">Cabin Occupancy Ratio</h3>
            <div class="flex-grow flex items-center justify-center">
                <canvas id="classOccupancyChart" class="max-h-[220px] max-w-[220px]"></canvas>
            </div>
        </div>
    </div>

    <!-- Bottom Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Popular Routes & Airline Share -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Popular Routes -->
            <div class="glass-card rounded-2xl p-6 border border-slate-900">
                <h3 class="font-bold text-white text-sm uppercase tracking-wider mb-4 flex items-center">
                    <i data-lucide="compass" class="w-4 h-4 mr-2 text-cyan-400"></i> Top 5 Popular Routes
                </h3>
                <div class="space-y-4">
                    @if($topRoutes->isEmpty())
                        <p class="text-xs text-slate-500 text-center py-4">No route data available</p>
                    @else
                        @foreach($topRoutes as $route)
                            <div class="flex items-center justify-between p-3 bg-slate-950/40 border border-slate-900 rounded-xl">
                                <div>
                                    <span class="block text-sm font-bold text-white">
                                        {{ $route->departure_city }} &rarr; {{ $route->arrival_city }}
                                    </span>
                                    <span class="block text-[10px] text-slate-500 uppercase tracking-widest font-mono">
                                        {{ $route->departure_iata }} &bull; {{ $route->arrival_iata }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-cyan-400 font-bold text-sm">{{ $route->booking_count }} bookings</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Airline Revenue Share -->
            <div class="glass-card rounded-2xl p-6 border border-slate-900">
                <h3 class="font-bold text-white text-sm uppercase tracking-wider mb-4 flex items-center">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2 text-cyan-400"></i> Sales Share by Airline
                </h3>
                <canvas id="airlineShareChart" class="max-h-[200px] w-full"></canvas>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="lg:col-span-7">
            <div class="glass-card rounded-2xl p-6 border border-slate-900 h-full flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-white text-sm uppercase tracking-wider mb-4 flex items-center">
                        <i data-lucide="receipt" class="w-4 h-4 mr-2 text-cyan-400"></i> Recent Transactions
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-300">
                            <thead class="text-xs uppercase text-slate-500 border-b border-slate-900">
                                <tr>
                                    <th class="py-3 px-2">PNR</th>
                                    <th class="py-3 px-2">Customer</th>
                                    <th class="py-3 px-2">Route</th>
                                    <th class="py-3 px-2 text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/50">
                                @if($recentBookings->isEmpty())
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-xs text-slate-500">No recent transactions</td>
                                    </tr>
                                @else
                                    @foreach($recentBookings as $booking)
                                        <tr>
                                            <td class="py-3.5 px-2 font-mono font-bold text-white uppercase">{{ $booking->booking_code }}</td>
                                            <td class="py-3.5 px-2 truncate max-w-[120px]">{{ $booking->user->name }}</td>
                                            <td class="py-3.5 px-2">
                                                <span class="font-semibold block text-xs">{{ $booking->flight->departureAirport->iata_code }} &rarr; {{ $booking->flight->arrivalAirport->iata_code }}</span>
                                                <span class="text-[10px] text-slate-500 font-mono block">{{ $booking->flight->flight_number }}</span>
                                            </td>
                                            <td class="py-3.5 px-2 text-right font-bold text-cyan-400">IDR {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
        
        // 1. Weekly Sales Chart
        const weeklyCtx = document.getElementById('weeklySalesChart').getContext('2d');
        const weeklyData = @json($weeklySales);
        
        new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: weeklyData.map(item => item.day),
                datasets: [{
                    label: 'Revenue (IDR)',
                    data: weeklyData.map(item => item.sales),
                    borderColor: '#06b6d4',
                    backgroundColor: 'rgba(6, 182, 212, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        grid: { color: '#1e293b' },
                        ticks: { color: '#64748b', font: { family: 'Inter' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { family: 'Inter' } }
                    }
                }
            }
        });

        // 2. Class Occupancy Chart
        const classCtx = document.getElementById('classOccupancyChart').getContext('2d');
        const classData = @json($classOccupancy);
        
        new Chart(classCtx, {
            type: 'doughnut',
            data: {
                labels: classData.map(item => item.class.charAt(0).toUpperCase() + item.class.slice(1)),
                datasets: [{
                    data: classData.map(item => item.passenger_count),
                    backgroundColor: ['#10b981', '#f59e0b', '#06b6d4'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 } }
                    }
                }
            }
        });

        // 3. Airline Revenue Share Chart
        const airlineCtx = document.getElementById('airlineShareChart').getContext('2d');
        const airlineData = @json($revenuePerAirline);
        
        new Chart(airlineCtx, {
            type: 'bar',
            data: {
                labels: airlineData.map(item => item.name),
                datasets: [{
                    data: airlineData.map(item => item.total_sales),
                    backgroundColor: '#8b5cf6',
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { color: '#1e293b' },
                        ticks: { color: '#64748b' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });

    });
</script>
@endsection
