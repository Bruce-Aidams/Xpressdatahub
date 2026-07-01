@extends('layouts.admin')
@section('page-title', 'Minimum Top-up Configuration')
@section('page-description', 'Set minimum and maximum top-up amount for users')
@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Minimum Top-up</h1>
        <p class="text-sm text-slate-400 mt-1">Set minimum and maximum top-up amount for users</p>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-[#2563EB]"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
            </div>
            <h2 class="text-sm font-bold text-slate-800">Top-up Limits</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.config.minimum-topup.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Minimum Top-up Amount (GH&#8373;)</label>
                        <input type="number" name="minimum_amount" step="0.01" value="{{ $config['minimum_amount'] ?? 10 }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Maximum Top-up Amount (GH&#8373;) <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input type="number" name="maximum_amount" step="0.01" value="{{ $config['maximum_amount'] ?? '' }}" placeholder="No limit" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                        <p class="text-[10px] text-slate-400 mt-1">Leave empty for no upper limit</p>
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
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <h2 class="text-sm font-bold text-slate-800">Configuration History</h2>
        </div>
        <div class="p-6">
            <div class="space-y-2">
                @foreach($history as $h)
                    <div class="flex items-center justify-between text-xs text-slate-500 py-2 border-b border-slate-50 last:border-0">
                        <span class="font-medium text-slate-700">Min: GH&#8373;{{ number_format($h['minimum_amount'] ?? 0, 2) }}@if(!empty($h['maximum_amount'])) &mdash; Max: GH&#8373;{{ number_format($h['maximum_amount'], 2) }}@endif</span>
                        <span class="text-slate-400">{{ $h['updated_at'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
