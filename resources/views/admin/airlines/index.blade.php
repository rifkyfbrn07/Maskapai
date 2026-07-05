@extends('layouts.app')

@section('title', 'Airlines')

@section('content')
<div class="py-4">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-6 border-b border-slate-900">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-cyan-400 hover:text-cyan-300 transition mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to admin dashboard
            </a>
            <h1 class="text-3xl font-extrabold text-white font-display">Airlines</h1>
            <p class="text-sm text-slate-400 mt-1">Manage carrier profiles and identity logos</p>
        </div>
        <a href="{{ route('airlines.create') }}" class="mt-4 md:mt-0 bg-cyan-500 hover:bg-cyan-400 text-sm font-bold text-white px-5 py-2.5 rounded-xl transition flex items-center shadow-lg shadow-cyan-950/20">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Airline
        </a>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-2xl overflow-hidden border border-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase text-slate-500 bg-slate-950/50 border-b border-slate-900">
                    <tr>
                        <th class="py-4 px-6">Logo</th>
                        <th class="py-4 px-6">Airline Name</th>
                        <th class="py-4 px-6">Code</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/50">
                    @if($airlines->isEmpty())
                        <tr>
                            <td colspan="4" class="py-8 text-center text-xs text-slate-500">No airlines registered yet.</td>
                        </tr>
                    @else
                        @foreach($airlines as $airline)
                            <tr class="hover:bg-slate-900/10 transition">
                                <td class="py-4 px-6">
                                    <div class="w-10 h-10 bg-white/5 border border-slate-850 rounded-xl flex items-center justify-center p-1">
                                        @if($airline->logo)
                                            <img src="{{ $airline->logo }}" alt="" class="max-w-full max-h-full object-contain">
                                        @else
                                            <i data-lucide="plane" class="w-4 h-4 text-slate-600"></i>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-semibold text-white">{{ $airline->name }}</td>
                                <td class="py-4 px-6 font-mono font-bold text-cyan-400 uppercase tracking-widest">{{ $airline->code }}</td>
                                <td class="py-4 px-6 text-right flex justify-end space-x-3 items-center">
                                    <a href="{{ route('airlines.edit', $airline->id) }}" class="p-1.5 bg-slate-900 border border-slate-800 text-slate-400 hover:text-cyan-400 rounded-lg hover:border-cyan-500/20 transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('airlines.destroy', $airline->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this airline?')" class="inline">
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
