@extends('layouts.admin')
@section('page-title', 'Payment Configuration')
@section('page-description', 'Configure payment gateway settings')
@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Payment Configuration</h1>
        <p class="text-sm text-slate-400 mt-1">Configure payment gateway settings</p>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                <x-heroicon-o-credit-card class="w-5 h-5 text-[#2563EB]" />
            </div>
            <h2 class="text-sm font-bold text-slate-800">Payment Gateway Settings</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.config.payment.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Payment Phone Number</label>
                        <input type="text" name="payment_phone_number" value="{{ $configs['payment_phone_number'] ?? '' }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Payment Name</label>
                        <input type="text" name="payment_name" value="{{ $configs['payment_name'] ?? '' }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">WhatsApp Contact</label>
                        <input type="text" name="whatsapp_contact" value="{{ $configs['whatsapp_contact'] ?? '' }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">WhatsApp Group Link</label>
                        <input type="url" name="whatsapp_group_link" value="{{ $configs['whatsapp_group_link'] ?? '' }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
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
