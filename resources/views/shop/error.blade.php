@extends('layouts.app')
@section('title', 'Order Error')
@section('body')
<div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-16 h-16 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-x-circle class="w-8 h-8 text-red-500" />
                </div>
                <h1 class="text-xl font-black text-slate-800">Something Went Wrong</h1>

                @if(session('error'))
                    <p class="text-sm text-red-500 mt-2">{{ session('error') }}</p>
                @else
                    <p class="text-sm text-slate-400 mt-2">We couldn't process your request. Please try again.</p>
                @endif
            </div>

            <div class="px-6 pb-6">
                <a href="{{ url()->previous() }}"
                   class="block w-full text-center bg-[#FF7A00] hover:bg-[#E06B00] text-white text-sm font-bold rounded-xl px-4 py-2.5 transition shadow-md shadow-orange-500/10">
                    Try Again
                </a>
                <p class="text-center text-xs text-slate-400 mt-3">If this persists, please contact support.</p>
            </div>
        </div>
    </div>
</div>
@endsection
