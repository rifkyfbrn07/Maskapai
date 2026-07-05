@extends('layouts.app')

@section('title', 'Airports Hubs')

@section('content')
<div class="py-4">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-6 border-b border-slate-900">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-cyan-400 hover:text-cyan-300 transition mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to admin dashboard
            </a>
            <h1 class="text-3xl font-extrabold text-white font-display">Airports Hubs</h1>
            <p class="text-sm text-slate-400 mt-1">Manage departure and arrival airports on the system</p>
        </div>
        <a href="{{ route('airports.create') }}" class="mt-4 md:mt-0 bg-cyan-500 hover:bg-cyan-400 text-sm font-bold text-white px-5 py-2.5 rounded-xl transition flex items-center shadow-lg shadow-cyan-950/20">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Airport
        </a>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-2xl overflow-hidden border border-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase text-slate-500 bg-slate-950/50 border-b border-slate-900">
                    <tr>
                        <th class="py-4 px-6">IATA Code</th>
                        <th class="py-4 px-6">Airport Name</th>
                        <th class="py-4 px-6">City</th>
                        <th class="py-4 px-6">Country</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/50">
                    @if($airports->isEmpty())
                        <tr>
                            <td colspan="5" class="py-8 text-center text-xs text-slate-500">No airports registered yet.</td>
                        </tr>
                    @else
                        @foreach($airports as $airport)
                            <tr class="hover:bg-slate-900/10 transition">
                                <td class="py-4 px-6 font-mono font-bold text-cyan-400 uppercase tracking-widest">{{ $airport->iata_code }}</td>
                                <td class="py-4 px-6 font-semibold text-white">{{ $airport->name }}</td>
                                <td class="py-4 px-6">{{ $airport->city }}</td>
                                <td class="py-4 px-6">{{ $airport->country }}</td>
                                <td class="py-4 px-6 text-right flex justify-end space-x-3 items-center">
                                    <a href="{{ route('airports.edit', $airport->id) }}" class="p-1.5 bg-slate-900 border border-slate-800 text-slate-400 hover:text-cyan-400 rounded-lg hover:border-cyan-500/20 transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('airports.destroy', $airport->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this airport?')" class="inline">
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
