@extends('layouts.admin')
@section('page-title', 'Low Balance Alert Configuration')
@section('page-description', 'Configure low balance alert thresholds')
@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Low Balance Alert</h1>
        <p class="text-sm text-slate-400 mt-1">Configure low balance alert thresholds</p>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                <x-heroicon-o-bell-alert class="w-5 h-5 text-[#2563EB]" />
            </div>
            <h2 class="text-sm font-bold text-slate-800">Low Balance Alert Settings</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.config.low-balance-alert.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Enabled</label>
                        <select name="enabled" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                            <option value="1" {{ ($config['enabled'] ?? 0) ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !($config['enabled'] ?? 0) ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Low Balance Threshold (GH₵)</label>
                        <input type="number" name="threshold_amount" step="0.01" value="{{ $config['threshold_amount'] ?? 50 }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Alert Interval (days)</label>
                        <input type="number" name="alert_interval_days" value="{{ $config['alert_interval_days'] ?? 1 }}" min="1" max="30" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
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
