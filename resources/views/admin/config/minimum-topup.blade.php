@extends('layouts.admin')
@section('page-title', 'Minimum Top-up Configuration')
@section('page-description', 'Set minimum top-up amount for users')
@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Minimum Top-up</h1>
        <p class="text-sm text-slate-400 mt-1">Set minimum top-up amount for users</p>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                <x-heroicon-o-banknotes class="w-5 h-5 text-[#2563EB]" />
            </div>
            <h2 class="text-sm font-bold text-slate-800">Minimum Top-up Settings</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.config.minimum-topup.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Minimum Top-up Amount (GH₵)</label>
                        <input type="number" name="minimum_amount" step="0.01" value="{{ $config['minimum_amount'] ?? 10 }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl px-6 py-2.5 transition shadow-md shadow-blue-500/10">Save Configuration</button>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($history))
    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden mt-4">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center">
                <x-heroicon-o-clock class="w-5 h-5 text-slate-400" />
            </div>
            <h2 class="text-sm font-bold text-slate-800">Configuration History</h2>
        </div>
        <div class="p-6">
            <div class="space-y-2">
                @foreach($history as $h)
                    <div class="flex items-center justify-between text-xs text-slate-500 py-2 border-b border-slate-50 last:border-0">
                        <span class="font-medium text-slate-700">Min: GH₵{{ number_format($h['minimum_amount'] ?? 0, 2) }}</span>
                        <span class="text-slate-400">{{ $h['updated_at'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
