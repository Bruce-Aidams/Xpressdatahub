@extends('layouts.admin')
@section('page-title', 'Referral Commission Configuration')
@section('page-description', 'Configure referral commission settings')
@section('content')
@php
    $getValue = function($key) use ($configs) {
        $item = $configs->where('config_key', $key)->first();
        return $item->config_value ?? '';
    };
@endphp
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Referral Commission</h1>
        <p class="text-sm text-slate-400 mt-1">Configure referral commission settings</p>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                <x-heroicon-o-users class="w-5 h-5 text-[#2563EB]" />
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
                        <input type="number" name="commission_percentage" step="0.01" min="0" max="100" value="{{ $getValue('commission_percentage') }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Min Orders Required</label>
                        <input type="number" name="min_orders_required" min="0" value="{{ $getValue('min_orders_required') }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Max Commission Per Order (GH₵)</label>
                        <input type="number" name="max_commission_per_order" step="0.01" min="0" value="{{ $getValue('max_commission_per_order') }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Enabled</label>
                        <select name="is_enabled" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                            <option value="1" {{ $getValue('is_enabled') == '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ $getValue('is_enabled') != '1' ? 'selected' : '' }}>No</option>
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
