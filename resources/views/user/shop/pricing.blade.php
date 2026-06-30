�@extends('layouts.user')
@section('title', 'Shop Pricing')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('user.shop.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400 hover:text-[#EA580C] transition mb-2">
                <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
                Back to Shop
            </a>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Shop Pricing</h1>
            <p class="text-sm text-slate-400 mt-0.5">Manage your selling prices for each network and package</p>
        </div>
    </div>

    {{-- Stats Row --}}
    @php
        $totalPackages = $pricing->count();
        $avgMargin = $totalPackages > 0 ? $pricing->avg('profit') : 0;
        $highestPrice = $totalPackages > 0 ? $pricing->max('selling_price') : 0;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                <x-heroicon-o-cube class="w-5 h-5 text-blue-500" />
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Packages</p>
                <p class="text-xl font-bold text-slate-800">{{ $totalPackages }}</p>
            </div>
        </div>
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                <x-heroicon-o-chart-bar class="w-5 h-5 text-emerald-500" />
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Average Margin</p>
                <p class="text-xl font-bold text-slate-800">GH₵{{ number_format($avgMargin, 2) }}</p>
            </div>
        </div>
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-amber-500" />
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Highest Price</p>
                <p class="text-xl font-bold text-slate-800">GH₵{{ number_format($highestPrice, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Pricing Table Card --}}
    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800">Pricing Overview</h3>
            <span class="text-xs text-slate-400">{{ $totalPackages }} {{ Str::plural('package', $totalPackages) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Network</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Package</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Base Price</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Your Price</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Profit</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80">
                    @forelse($pricing as $item)
                        @php
                            $marginPercent = $item->base_price > 0 ? (($item->selling_price - $item->base_price) / $item->base_price) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-orange-50/20 transition">
                            <td class="px-6 py-4">
                                <x-network-badge :network="$item->network_type" />
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ $item->package_size }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">GH₵{{ number_format($item->base_price, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-700">GH₵{{ number_format($item->selling_price, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-emerald-500">GH₵{{ number_format($item->profit, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                    {{ number_format($marginPercent, 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                                        <x-heroicon-o-tag class="w-6 h-6 text-slate-400" />
                                    </div>
                                    <p class="text-sm font-medium text-slate-500">No pricing configured</p>
                                    <p class="text-xs text-slate-400">Pricing rules will appear here once set up</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Info Tip Card --}}
    <div class="bg-gradient-to-r from-orange-50/80 to-amber-50/50 border border-orange-100/60 rounded-2xl p-5 flex items-start gap-3.5">
        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center mt-0.5">
            <x-heroicon-o-information-circle class="w-4.5 h-4.5 text-[#EA580C]" />
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-700">Pricing Tip</p>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Your selling price must always be higher than the base price to earn a profit. Competitive margins typically range between 5-15%. You can adjust your prices anytime from the shop settings.</p>
        </div>
    </div>

</div>
@endsection
