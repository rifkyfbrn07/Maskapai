@extends('layouts.app')

@section('title', 'Edit Airline')

@section('content')
<div class="max-w-xl mx-auto py-4">
    <div class="mb-8 pb-6 border-b border-slate-900">
        <a href="{{ route('airlines.index') }}" class="inline-flex items-center text-sm font-medium text-cyan-400 hover:text-cyan-300 transition mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to list
        </a>
        <h1 class="text-3xl font-extrabold text-white font-display">Edit Airline</h1>
        <p class="text-sm text-slate-400 mt-1">Modify records for airline brand {{ $airline->name }} ({{ $airline->code }})</p>
    </div>

    <div class="glass-card rounded-2xl p-6 border border-slate-900 relative overflow-hidden">
        <form action="{{ route('airlines.update', $airline->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Airline Name -->
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-300 mb-2">Airline Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $airline->name) }}" required autofocus
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                    placeholder="Garuda Indonesia">
                @error('name')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Code -->
            <div>
                <label for="code" class="block text-sm font-semibold text-slate-300 mb-2">Airline Code (2-3 Characters)</label>
                <input type="text" name="code" id="code" value="{{ old('code', $airline->code) }}" required maxlength="10"
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm font-mono tracking-widest focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                    placeholder="GA">
                @error('code')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Logo URL -->
            <div>
                <label for="logo" class="block text-sm font-semibold text-slate-300 mb-2">Logo URL (Optional)</label>
                <input type="url" name="logo" id="logo" value="{{ old('logo', $airline->logo) }}"
                    class="block w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                    placeholder="https://example.com/logo.png">
                @error('logo')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full mt-4 py-3 px-4 bg-cyan-500 hover:bg-cyan-400 text-sm font-bold text-white rounded-xl shadow-lg shadow-cyan-950/20 glow-cyan transition">
                Update Airline
            </button>
        </form>
    </div>
</div>
@endsection
