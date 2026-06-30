@extends('layouts.app')
@section('title', '403 - Forbidden')
@section('body')
<div class="min-h-screen flex items-center justify-center bg-slate-50 px-4">
    <div class="text-center">
        <div class="text-8xl font-extrabold text-slate-200 mb-4">403</div>
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Access Denied</h1>
        <p class="text-slate-400 text-sm mb-6">You don't have permission to access this page.</p>
        <div class="flex gap-3 justify-center">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#FF7A00] hover:bg-[#E06B00] text-white text-sm font-bold rounded-xl transition">
                <x-heroicon-o-home class="w-5 h-5" /> Go Home
            </a>
            <button onclick="history.back()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-bold rounded-xl transition">
                <x-heroicon-o-arrow-left class="w-5 h-5" /> Go Back
            </button>
        </div>
    </div>
</div>
@endsection
