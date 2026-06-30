@extends('layouts.admin')
@section('page-title', 'Data Integration Configuration')
@section('page-description', 'Configure data integration settings')
@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Data Integration</h1>
        <p class="text-sm text-slate-400 mt-1">Configure data integration settings</p>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                <x-heroicon-o-globe-alt class="w-5 h-5 text-[#2563EB]" />
            </div>
            <h2 class="text-sm font-bold text-slate-800">Data Integration Settings</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.config.data-integration.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Data Website URL</label>
                        <input type="url" name="data_website_url" value="{{ $config['data_website_url'] ?? '' }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Data Website API Key</label>
                        <input type="password" name="data_website_api_key" value="{{ $config['data_website_api_key'] ?? '' }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Webhook URL</label>
                        <input type="url" name="webhook_url" value="{{ $config['webhook_url'] ?? '' }}" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Enabled</label>
                        <select name="enabled" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                            <option value="1" {{ ($config['enabled'] ?? 0) ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !($config['enabled'] ?? 0) ? 'selected' : '' }}>No</option>
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
