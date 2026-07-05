@extends('layouts.app')

@section('title', 'Airplanes Fleet')

@section('content')
<div class="py-4">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-6 border-b border-slate-900">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-cyan-400 hover:text-cyan-300 transition mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to admin dashboard
            </a>
            <h1 class="text-3xl font-extrabold text-white font-display">Airplanes Fleet</h1>
            <p class="text-sm text-slate-400 mt-1">Manage carrier fleets, capacity, and seating configurations</p>
        </div>
        <a href="{{ route('airplanes.create') }}" class="mt-4 md:mt-0 bg-cyan-500 hover:bg-cyan-400 text-sm font-bold text-white px-5 py-2.5 rounded-xl transition flex items-center shadow-lg shadow-cyan-950/20">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Airplane
        </a>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-2xl overflow-hidden border border-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase text-slate-500 bg-slate-950/50 border-b border-slate-900">
                    <tr>
                        <th class="py-4 px-6">ID</th>
                        <th class="py-4 px-6">Airline</th>
                        <th class="py-4 px-6">Aircraft Model</th>
                        <th class="py-4 px-6">Capacity (Seats)</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/50">
                    @if($airplanes->isEmpty())
                        <tr>
                            <td colspan="5" class="py-8 text-center text-xs text-slate-500">No airplanes registered in fleet yet.</td>
                        </tr>
                    @else
                        @foreach($airplanes as $airplane)
                            <tr class="hover:bg-slate-900/10 transition">
                                <td class="py-4 px-6 font-mono text-xs text-slate-500">#{{ $airplane->id }}</td>
                                <td class="py-4 px-6 font-bold text-white">{{ $airplane->airline->name }}</td>
                                <td class="py-4 px-6 font-semibold text-slate-300">{{ $airplane->model }}</td>
                                <td class="py-4 px-6 font-semibold text-cyan-400 font-mono">{{ $airplane->capacity }} seats</td>
                                <td class="py-4 px-6 text-right flex justify-end space-x-3 items-center">
                                    <a href="{{ route('airplanes.edit', $airplane->id) }}" class="p-1.5 bg-slate-900 border border-slate-800 text-slate-400 hover:text-cyan-400 rounded-lg hover:border-cyan-500/20 transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('airplanes.destroy', $airplane->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this airplane? This will also wipe its seat map layout!')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-slate-900 border border-slate-800 text-slate-400 hover:text-red-400 rounded-lg hover:border-red-500/20 transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
