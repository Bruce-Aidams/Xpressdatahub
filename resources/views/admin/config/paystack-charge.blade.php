@extends('layouts.admin')
@section('page-title', 'Paystack Charge Configuration')
@section('page-description', 'Configure Paystack transaction charges')
@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Paystack Charge</h1>
        <p class="text-sm text-slate-400 mt-1">Configure Paystack transaction charges</p>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                <x-heroicon-o-receipt-percent class="w-5 h-5 text-[#2563EB]" />
            </div>
            <h2 class="text-sm font-bold text-slate-800">Paystack Charge Settings</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.config.paystack-charge.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Charge Type</label>
                        <select name="charge_type" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                            <option value="percentage" {{ ($chargeConfig['charge_type'] ?? '') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                            <option value="fixed" {{ ($chargeConfig['charge_type'] ?? '') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Charge Amount</label>
                        <input type="number" name="charge_amount" step="0.01" value="{{ $chargeConfig['charge_amount'] ?? '' }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
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
