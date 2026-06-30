@extends('layouts.app')
@section('title', '404 - Page Not Found')
@section('body')
<div class="min-h-screen flex items-center justify-center bg-slate-50 px-4">
    <div class="text-center">
        <div class="text-8xl font-extrabold text-slate-200 mb-4">404</div>
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Page Not Found</h1>
        <p class="text-slate-400 text-sm mb-6">The page you are looking for doesn't exist or has been moved.</p>
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#FF7A00] hover:bg-[#E06B00] text-white text-sm font-bold rounded-xl transition">
            <x-heroicon-o-home class="w-5 h-5" /> Go Home
        </a>
    </div>
</div>
@endsection
