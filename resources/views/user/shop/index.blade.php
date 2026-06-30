�@extends('layouts.user')

@section('title', 'My Shop')
@section('page-title', 'My Shop')
@section('page-description', 'Manage your shop settings and track performance')

@section('content')
{{-- Page Header --}}
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-[#EA580C]/10 flex items-center justify-center shrink-0">
                <x-heroicon-o-building-storefront class="w-5 h-5 text-[#EA580C]" />
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-bold text-slate-800 truncate">{{ $shop->name ?? 'My Shop' }}</h1>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ ($shop->is_active ?? true) ? 'bg-emerald-50 text-emerald-600 border border-emerald-200/60' : 'bg-red-50 text-red-600 border border-red-200/60' }}">
                        {{ ($shop->is_active ?? true) ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Manage your shop settings and track performance</p>
            </div>
        </div>
        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200/60 rounded-xl px-3 py-2">
            <span class="text-xs text-slate-400 shrink-0">Public Link:</span>
            <span id="shopLink" class="text-xs font-medium text-slate-600 truncate max-w-[200px]">{{ url('shop/' . $shop->shop_slug) }}</span>
            <button onclick="copyShopLink()" class="shrink-0 p-1 rounded-lg hover:bg-slate-200/60 transition-colors" title="Copy link">
                <x-heroicon-o-clipboard-document class="w-4 h-4 text-slate-400" id="copyIcon" />
            </button>
        </div>
    </div>
</div>

{{-- Stat Cards Row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
    {{-- Total Orders --}}
    <div class="bg-white border border-slate-100 rounded-2xl p-4 sm:p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                <x-heroicon-o-shopping-bag class="w-5 h-5 text-blue-500" />
            </div>
            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Total Orders</span>
        </div>
        <p class="text-2xl font-bold text-slate-800 tabular-nums">{{ number_format($shop->total_orders ?? 0) }}</p>
    </div>

    {{-- Revenue --}}
    <div class="bg-white border border-slate-100 rounded-2xl p-4 sm:p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-[#EA580C]/10 flex items-center justify-center">
                <x-heroicon-o-currency-dollar class="w-5 h-5 text-[#EA580C]" />
            </div>
            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Revenue</span>
        </div>
        <p class="text-2xl font-bold text-slate-800 tabular-nums">GH₵{{ number_format($shop->revenue ?? 0, 2) }}</p>
    </div>

    {{-- Available Balance --}}
    <div class="bg-white border border-slate-100 rounded-2xl p-4 sm:p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
            </div>
            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Available Balance</span>
        </div>
        <p class="text-2xl font-bold text-emerald-600 tabular-nums">GH₵{{ number_format($earningsSummary['available_balance'] ?? 0, 2) }}</p>
    </div>

    {{-- Total Profit --}}
    <div class="bg-white border border-slate-100 rounded-2xl p-4 sm:p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center">
                <x-heroicon-o-chart-bar class="w-5 h-5 text-purple-500" />
            </div>
            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Total Profit</span>
        </div>
        <p class="text-2xl font-bold text-slate-800 tabular-nums">GH₵{{ number_format($earningsSummary['credited_profit'] ?? 0, 2) }}</p>
    </div>
</div>

{{-- Two-Column Layout --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">

    {{-- Left Column: Shop Settings --}}
    <div class="lg:col-span-2">
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm">
            {{-- Section Header --}}
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                        <x-heroicon-o-cog-6-tooth class="w-4 h-4 text-slate-500" />
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Shop Settings</h3>
                </div>
            </div>

            <form method="POST" action="{{ route('user.shop.update') }}">
                @csrf
                @method('PUT')

                <div class="p-5 sm:p-6 space-y-5">
                    {{-- Shop Name --}}
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-600 mb-1.5">Shop Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ $shop->name ?? '' }}"
                            placeholder="Enter your shop name"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-300 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all"
                        >
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-600 mb-1.5">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Describe your shop..."
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-300 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all resize-none"
                        >{{ $shop->description ?? '' }}</textarea>
                    </div>

                    {{-- WhatsApp Number --}}
                    <div>
                        <label for="whatsapp_number" class="block text-xs font-semibold text-slate-600 mb-1.5">WhatsApp Number</label>
                        <input
                            type="text"
                            id="whatsapp_number"
                            name="whatsapp_number"
                            value="{{ $settings->whatsapp_number ?? '' }}"
                            placeholder="+233 XX XXX XXXX"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-300 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all"
                        >
                    </div>

                    {{-- WhatsApp Group Link --}}
                    <div>
                        <label for="whatsapp_group_link" class="block text-xs font-semibold text-slate-600 mb-1.5">WhatsApp Group Link</label>
                        <input
                            type="url"
                            id="whatsapp_group_link"
                            name="whatsapp_group_link"
                            value="{{ $settings->whatsapp_group_link ?? '' }}"
                            placeholder="https://chat.whatsapp.com/..."
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-300 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all"
                        >
                    </div>

                    {{-- Working Hours --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-2">Working Hours</label>
                        @php
                            $workingHours = is_string($settings->working_hours ?? null) ? json_decode($settings->working_hours, true) : ($settings->working_hours ?? []);
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="wh_open" class="block text-[11px] text-slate-400 mb-1">Opening Time</label>
                                <input
                                    type="time"
                                    id="wh_open"
                                    name="working_hours[open]"
                                    value="{{ $workingHours['open'] ?? '08:00' }}"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all"
                                >
                            </div>
                            <div>
                                <label for="wh_close" class="block text-[11px] text-slate-400 mb-1">Closing Time</label>
                                <input
                                    type="time"
                                    id="wh_close"
                                    name="working_hours[close]"
                                    value="{{ $workingHours['close'] ?? '18:00' }}"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="px-5 sm:px-6 py-4 border-t border-slate-100 flex justify-end">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-sm shadow-orange-500/15"
                    >
                        <x-heroicon-o-check class="w-4 h-4" />
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Right Column: Quick Actions --}}
    <div class="lg:col-span-1">
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#EA580C]/10 flex items-center justify-center">
                        <x-heroicon-o-bolt class="w-4 h-4 text-[#EA580C]" />
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Quick Actions</h3>
                </div>
            </div>

            <div class="p-2">
                {{-- Manage Pricing --}}
                <a href="{{ route('user.shop.pricing') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-50 transition-colors group">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 group-hover:bg-blue-100 transition-colors">
                        <x-heroicon-o-calculator class="w-5 h-5 text-blue-500" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors">Manage Pricing</p>
                        <p class="text-[11px] text-slate-400">Set prices for your packages</p>
                    </div>
                    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300 shrink-0 group-hover:text-slate-400 transition-colors" />
                </a>

                {{-- View Earnings --}}
                <a href="{{ route('user.shop-profits.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-50 transition-colors group">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition-colors">
                        <x-heroicon-o-currency-dollar class="w-5 h-5 text-emerald-500" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors">View Earnings</p>
                        <p class="text-[11px] text-slate-400">Track your profit history</p>
                    </div>
                    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300 shrink-0 group-hover:text-slate-400 transition-colors" />
                </a>

                {{-- Public Shop --}}
                <a href="{{ url('shop/' . $shop->shop_slug) }}" target="_blank" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-50 transition-colors group">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center shrink-0 group-hover:bg-purple-100 transition-colors">
                        <x-heroicon-o-globe-alt class="w-5 h-5 text-purple-500" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors">Public Shop</p>
                        <p class="text-[11px] text-slate-400">Preview your storefront</p>
                    </div>
                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 text-slate-300 shrink-0 group-hover:text-slate-400 transition-colors" />
                </a>

                {{-- Withdraw Funds --}}
                <a href="{{ route('user.shop-profits.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-50 transition-colors group">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0 group-hover:bg-amber-100 transition-colors">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-amber-500" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors">Withdraw Funds</p>
                        <p class="text-[11px] text-slate-400">Cash out your earnings</p>
                    </div>
                    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300 shrink-0 group-hover:text-slate-400 transition-colors" />
                </a>
            </div>
        </div>

        {{-- Earnings Breakdown Mini Card --}}
        <div class="mt-4 bg-white border border-slate-100 rounded-2xl shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                        <x-heroicon-o-chart-pie class="w-4 h-4 text-slate-500" />
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Earnings Breakdown</h3>
                </div>
            </div>
            <div class="p-5 space-y-3.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Credited Profit</span>
                    <span class="text-sm font-bold text-slate-800 tabular-nums">GH₵{{ number_format($earningsSummary['credited_profit'] ?? 0, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Pending Profit</span>
                    <span class="text-sm font-bold text-amber-500 tabular-nums">GH₵{{ number_format($earningsSummary['pending_profit'] ?? 0, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Withdrawn</span>
                    <span class="text-sm font-bold text-blue-500 tabular-nums">GH₵{{ number_format($earningsSummary['withdrawn_or_reserved'] ?? 0, 2) }}</span>
                </div>
                <div class="border-t border-slate-100 pt-3 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-600">Available Balance</span>
                    <span class="text-sm font-bold text-emerald-600 tabular-nums">GH₵{{ number_format($earningsSummary['available_balance'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Earnings --}}
<div class="bg-white border border-slate-100 rounded-2xl shadow-sm">
    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                <x-heroicon-o-clock class="w-4 h-4 text-slate-500" />
            </div>
            <h3 class="text-sm font-bold text-slate-800">Recent Earnings</h3>
        </div>
        <a href="{{ route('user.shop-profits.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#EA580C] hover:text-[#C2410C] transition-colors">
            View All
            <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
        </a>
    </div>

    @php
        $recentEarnings = $shop->earnings()->latest()->take(5)->get();
    @endphp

    @if($recentEarnings->isEmpty())
        <div class="p-8 sm:p-12 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <x-heroicon-o-inbox class="w-7 h-7 text-slate-300" />
            </div>
            <p class="text-sm font-semibold text-slate-500 mb-1">No earnings yet</p>
            <p class="text-xs text-slate-400">Your earnings will appear here once orders start rolling in.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 sm:px-6 py-3">Order Ref</th>
                        <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 sm:px-6 py-3">Package</th>
                        <th class="text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 sm:px-6 py-3">Amount</th>
                        <th class="text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 sm:px-6 py-3">Profit</th>
                        <th class="text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 sm:px-6 py-3">Status</th>
                        <th class="text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 sm:px-6 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($recentEarnings as $earning)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 sm:px-6 py-3.5">
                                <span class="text-xs font-semibold text-slate-700">{{ $earning->order_reference ?? 'N/A' }}</span>
                            </td>
                            <td class="px-5 sm:px-6 py-3.5">
                                <span class="text-xs text-slate-600">{{ $earning->package_size ?? '-' }}</span>
                            </td>
                            <td class="px-5 sm:px-6 py-3.5 text-right">
                                <span class="text-xs font-semibold text-slate-700 tabular-nums">GH&#8373;{{ number_format($earning->selling_price ?? 0, 2) }}</span>
                            </td>
                            <td class="px-5 sm:px-6 py-3.5 text-right">
                                <span class="text-xs font-bold text-emerald-600 tabular-nums">GH₵{{ number_format($earning->profit ?? 0, 2) }}</span>
                            </td>
                            <td class="px-5 sm:px-6 py-3.5 text-center">
                                @php
                                    $statusColors = [
                                        'credited' => 'bg-emerald-50 text-emerald-600 border-emerald-200/60',
                                        'pending' => 'bg-amber-50 text-amber-600 border-amber-200/60',
                                        'withdrawn' => 'bg-blue-50 text-blue-600 border-blue-200/60',
                                        'cancelled' => 'bg-red-50 text-red-600 border-red-200/60',
                                    ];
                                    $status = strtolower($earning->status ?? 'pending');
                                    $colorClass = $statusColors[$status] ?? 'bg-slate-50 text-slate-500 border-slate-200/60';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $colorClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="px-5 sm:px-6 py-3.5 text-right">
                                <span class="text-xs text-slate-400">{{ $earning->created_at?->format('M d, Y') ?? '-' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function copyShopLink() {
        var link = document.getElementById('shopLink').textContent.trim();
        var icon = document.getElementById('copyIcon');
        navigator.clipboard.writeText(link).then(function() {
            icon.classList.add('text-emerald-500');
            icon.classList.remove('text-slate-400');
            setTimeout(function() {
                icon.classList.remove('text-emerald-500');
                icon.classList.add('text-slate-400');
            }, 1500);
        });
    }
</script>
@endpush
@endsection
