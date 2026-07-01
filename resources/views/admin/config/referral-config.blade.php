@extends('layouts.admin')
@section('page-title', 'Referral Commission Configuration')
@section('page-description', 'Configure referral commission settings')
@section('content')
@php
    $config = $configs->first();
@endphp
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Referral Commission</h1>
        <p class="text-sm text-slate-400 mt-1">Configure referral commission settings</p>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-[#2563EB]"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
            </div>
            <h2 class="text-sm font-bold text-slate-800">Referral Commission Settings</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.config.referral.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Commission Percentage (%)</label>
                        <input type="number" name="commission_percentage" step="0.01" min="0" max="100" value="{{ $config->commission_percentage ?? '' }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Min Orders Required</label>
                        <input type="number" name="min_orders_required" min="0" value="{{ $config->min_orders_required ?? '' }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Max Commission Per Order (GH&#8373;)</label>
                        <input type="number" name="max_commission_per_order" step="0.01" min="0" value="{{ $config->max_commission_per_order ?? '' }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Enabled</label>
                        <select name="is_enabled" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                            <option value="1" {{ ($config->is_enabled ?? false) ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !($config->is_enabled ?? false) ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl px-6 py-2.5 transition shadow-md shadow-blue-500/10">Save Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
