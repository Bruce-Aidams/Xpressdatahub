@extends('layouts.admin')
@section('page-title', $shop->name ?? 'Shop Detail')
@section('page-description', 'Manage shop details, pricing, earnings, and withdrawals')

@php
    $availableBalance = $totalEarnings - $totalWithdrawn;
    $isActive = $shop->is_active ?? false;
@endphp

@section('content')

{{-- Back Link --}}
<a href="{{ route('admin.shops.index') }}"
   class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-[#2563EB] transition mb-4">
    <x-heroicon-o-arrow-left class="w-4 h-4" />
    Back to Shops
</a>

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-[#2563EB]/10 flex items-center justify-center text-[#2563EB] font-black text-sm shrink-0">
            {{ strtoupper(substr($shop->name ?? 'S', 0, 2)) }}
        </div>
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl font-black text-slate-800">{{ $shop->name ?? 'Shop' }}</h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $isActive ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                    {{ $isActive ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">Shop ID: #{{ $shop->id }} &middot; {{ $shop->shop_slug ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('admin.shops.status', $shop->id) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="is_active" value="{{ $isActive ? '0' : '1' }}">
            <button type="submit"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-xl border transition
                    {{ $isActive
                        ? 'border-red-200 text-red-500 hover:bg-red-50 bg-red-50/50'
                        : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50 bg-emerald-50/50' }}">
                @if($isActive)
                    <x-heroicon-o-x-circle class="w-4 h-4" />
                    Deactivate Shop
                @else
                    <x-heroicon-o-check-circle class="w-4 h-4" />
                    Activate Shop
                @endif
            </button>
        </form>
        <button onclick="document.getElementById('deleteShopModal').classList.remove('hidden')"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-xl border border-slate-200 text-slate-400 hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition bg-white">
            <x-heroicon-o-trash class="w-4 h-4" />
            Delete Shop
        </button>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Total Earnings</p>
                <p class="text-xl font-black text-slate-800">GH&#8373;{{ number_format($totalEarnings, 2) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#2563EB]/10 flex items-center justify-center">
                <x-heroicon-o-currency-dollar class="w-5 h-5 text-[#2563EB]" />
            </div>
        </div>
    </div>
    <div class="stat-card bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pending Earnings</p>
                <p class="text-xl font-black text-amber-600">GH&#8373;{{ number_format($pendingEarnings, 2) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <x-heroicon-o-clock class="w-5 h-5 text-amber-500" />
            </div>
        </div>
    </div>
    <div class="stat-card bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Total Withdrawn</p>
                <p class="text-xl font-black text-blue-600">GH&#8373;{{ number_format($totalWithdrawn, 2) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                <x-heroicon-o-arrow-up-right class="w-5 h-5 text-blue-500" />
            </div>
        </div>
    </div>
    <div class="stat-card bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Available Balance</p>
                <p class="text-xl font-black {{ $availableBalance >= 0 ? 'text-emerald-600' : 'text-red-500' }}">GH&#8373;{{ number_format($availableBalance, 2) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
            </div>
        </div>
    </div>
</div>

{{-- Shop Details Card --}}
<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-800">Shop Details</h3>
    </div>
    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Shop Name</p>
            <p class="text-sm font-bold text-slate-800">{{ $shop->name ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Owner</p>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center text-slate-500 text-[9px] font-bold shrink-0">
                    {{ strtoupper(substr($shop->agent->username ?? 'NA', 0, 1)) }}
                </div>
                <p class="text-sm font-semibold text-slate-700">{{ $shop->agent->username ?? 'N/A' }}</p>
            </div>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Slug</p>
            <p class="text-sm font-mono text-slate-600">{{ $shop->shop_slug ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</p>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $isActive ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                {{ $isActive ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Total Orders</p>
            <p class="text-sm font-bold text-slate-800">{{ number_format($shop->earnings_count ?? $shop->earnings->count() ?? 0) }}</p>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Revenue</p>
            <p class="text-sm font-bold text-slate-800">GH&#8373;{{ number_format($totalEarnings, 2) }}</p>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">WhatsApp</p>
            <p class="text-sm font-semibold text-slate-700">{{ $shop->setting->whatsapp_number ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Working Hours</p>
            @php
                $wh = $shop->setting->working_hours ?? null;
                $workingHours = is_string($wh) ? json_decode($wh, true) : ($wh ?? []);
            @endphp
            @if(!empty($workingHours))
                <div class="space-y-1">
                    @foreach($workingHours as $day => $hours)
                        <div class="flex justify-between text-xs">
                            <span class="font-semibold text-slate-600 uppercase">{{ $day }}</span>
                            <span class="text-slate-500">
                                @if($hours['enabled'] ?? false)
                                    {{ $hours['open'] ?? '08:00' }} - {{ $hours['close'] ?? '18:00' }}
                                @else
                                    <span class="text-red-400">Closed</span>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm font-semibold text-slate-700">N/A</p>
            @endif
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Created</p>
            <p class="text-sm font-semibold text-slate-700">{{ $shop->created_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
    {{-- Tab Navigation --}}
    <div class="border-b border-slate-100 px-6">
        <nav class="flex gap-1 -mb-px" id="shopTabs">
            <button onclick="switchTab('pricing')"
                class="tab-btn px-4 py-3.5 text-xs font-bold border-b-2 transition whitespace-nowrap"
                data-tab="pricing">
                <x-heroicon-o-currency-dollar class="w-4 h-4 inline -mt-0.5 mr-1.5" />
                Pricing
            </button>
            <button onclick="switchTab('earnings')"
                class="tab-btn px-4 py-3.5 text-xs font-bold border-b-2 transition whitespace-nowrap"
                data-tab="earnings">
                <x-heroicon-o-chart-bar class="w-4 h-4 inline -mt-0.5 mr-1.5" />
                Earnings
            </button>
            <button onclick="switchTab('withdrawals')"
                class="tab-btn px-4 py-3.5 text-xs font-bold border-b-2 transition whitespace-nowrap"
                data-tab="withdrawals">
                <x-heroicon-o-arrow-up-right class="w-4 h-4 inline -mt-0.5 mr-1.5" />
                Withdrawals
            </button>
        </nav>
    </div>

    {{-- Tab: Pricing --}}
    <div id="tab-pricing" class="tab-content">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-slate-50/60 border-b border-slate-100">
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Network</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Package</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Base Price</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Selling Price</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Profit</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shop->pricing as $p)
                        <tr class="hover:bg-blue-50/20 transition">
                            <td class="px-6 py-3.5">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">{{ $p->network_type }}</span>
                            </td>
                            <td class="px-6 py-3.5 font-semibold text-slate-700 text-xs">{{ $p->package_size ?? $p->package_size_gb . ' GB' }}</td>
                            <td class="px-6 py-3.5 text-slate-500 text-right text-xs">GH&#8373;{{ number_format($p->base_price, 2) }}</td>
                            <td class="px-6 py-3.5 text-slate-800 font-bold text-right text-xs">GH&#8373;{{ number_format($p->selling_price, 2) }}</td>
                            <td class="px-6 py-3.5 text-right text-xs">
                                <span class="font-bold {{ $p->profit > 0 ? 'text-emerald-600' : 'text-slate-500' }}">GH&#8373;{{ number_format($p->profit, 2) }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <button onclick="openEditPricingModal({{ $p->id }}, '{{ $p->network_type }}', '{{ addslashes($p->package_size ?? $p->package_size_gb . ' GB') }}', {{ $p->base_price }}, {{ $p->selling_price }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-[10px] font-bold text-[#2563EB] bg-[#2563EB]/10 hover:bg-[#2563EB]/20 rounded-lg transition">
                                    <x-heroicon-o-pencil-square class="w-3 h-3" />
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <x-heroicon-o-currency-dollar class="w-6 h-6 text-slate-400" />
                                    </div>
                                    <p class="text-sm font-medium text-slate-400">No pricing configured</p>
                                    <p class="text-xs text-slate-300">Add pricing to this shop to get started.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tab: Earnings --}}
    <div id="tab-earnings" class="tab-content hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-slate-50/60 border-b border-slate-100">
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Order Ref</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Package</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Amount</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Profit</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shop->earnings->take(20) as $e)
                        @php $es = $e->status; @endphp
                        <tr class="hover:bg-blue-50/20 transition">
                            <td class="px-6 py-3.5 font-bold text-slate-800 text-xs">#{{ $e->order_reference ?? $e->order_id ?? 'N/A' }}</td>
                            <td class="px-6 py-3.5 text-slate-600 text-xs">{{ $e->package_size ?? 'N/A' }}</td>
                            <td class="px-6 py-3.5 text-slate-800 font-bold text-right text-xs">GH&#8373;{{ number_format($e->selling_price, 2) }}</td>
                            <td class="px-6 py-3.5 text-right text-xs">
                                <span class="font-bold {{ $e->profit > 0 ? 'text-emerald-600' : 'text-slate-500' }}">GH&#8373;{{ number_format($e->profit, 2) }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($es === 'credited')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-emerald-600 bg-emerald-50">
                                        <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                        Credited
                                    </span>
                                @elseif($es === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-amber-600 bg-amber-50">
                                        <span class="w-1 h-1 rounded-full bg-amber-500"></span>
                                        Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-slate-500 bg-slate-50">
                                        {{ ucfirst($es) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-slate-400 text-xs">{{ $e->credited_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <x-heroicon-o-chart-bar class="w-6 h-6 text-slate-400" />
                                    </div>
                                    <p class="text-sm font-medium text-slate-400">No earnings yet</p>
                                    <p class="text-xs text-slate-300">Earnings will appear here once orders are completed.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shop->earnings->count() > 20)
            <div class="px-6 py-3 border-t border-slate-100 text-center">
                <a href="{{ route('admin.shop-orders.index') }}" class="text-xs font-bold text-[#2563EB] hover:underline">View all {{ $shop->earnings->count() }} earnings &rarr;</a>
            </div>
        @endif
    </div>

    {{-- Tab: Withdrawals --}}
    <div id="tab-withdrawals" class="tab-content hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-slate-50/60 border-b border-slate-100">
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Amount</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Method</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Date</th>
                        <th class="px-6 py-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shop->withdrawals->take(20) as $w)
                        @php $ws = $w->status; @endphp
                        <tr class="hover:bg-blue-50/20 transition">
                            <td class="px-6 py-3.5 font-bold text-slate-800 text-xs">GH&#8373;{{ number_format($w->amount, 2) }}</td>
                            <td class="px-6 py-3.5 text-slate-600 text-xs">{{ ucfirst(str_replace('_', ' ', $w->payment_method ?? 'N/A')) }}</td>
                            <td class="px-6 py-3.5">
                                @if($ws === 'delivered')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-emerald-600 bg-emerald-50">
                                        <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                        Completed
                                    </span>
                                @elseif($ws === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-blue-600 bg-blue-50">
                                        <span class="w-1 h-1 rounded-full bg-blue-500"></span>
                                        Approved
                                    </span>
                                @elseif($ws === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-amber-600 bg-amber-50">
                                        <span class="w-1 h-1 rounded-full bg-amber-500"></span>
                                        Pending
                                    </span>
                                @elseif($ws === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-red-600 bg-red-50">
                                        <span class="w-1 h-1 rounded-full bg-red-500"></span>
                                        Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-slate-500 bg-slate-50">
                                        {{ ucfirst($ws) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-slate-400 text-xs">{{ $w->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                            <td class="px-6 py-3.5 text-right">
                                @if($ws === 'pending')
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form method="POST" action="{{ route('admin.shop-withdrawals.approve', $w->id) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition">
                                                <x-heroicon-o-check class="w-3 h-3" />
                                                Approve
                                            </button>
                                        </form>
                                        <button onclick="openRejectModal({{ $w->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                                            <x-heroicon-o-x-mark class="w-3 h-3" />
                                            Reject
                                        </button>
                                    </div>
                                @elseif($ws === 'approved')
                                    <form method="POST" action="{{ route('admin.shop-withdrawals.complete', $w->id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                            <x-heroicon-o-check-badge class="w-3 h-3" />
                                            Complete
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] text-slate-300 font-medium">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <x-heroicon-o-arrow-up-right class="w-6 h-6 text-slate-400" />
                                    </div>
                                    <p class="text-sm font-medium text-slate-400">No withdrawal requests</p>
                                    <p class="text-xs text-slate-300">Withdrawal requests will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shop->withdrawals->count() > 20)
            <div class="px-6 py-3 border-t border-slate-100 text-center">
                <a href="{{ route('admin.shop-withdrawals.index') }}" class="text-xs font-bold text-[#2563EB] hover:underline">View all {{ $shop->withdrawals->count() }} withdrawals &rarr;</a>
            </div>
        @endif
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteShopModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl shadow-slate-200/50 w-full max-w-md animate-fade-in">
        <div class="p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                <x-heroicon-o-exclamation-triangle class="w-7 h-7 text-red-500" />
            </div>
            <h3 class="text-lg font-black text-slate-800 mb-2">Delete Shop</h3>
            <p class="text-sm text-slate-500 mb-1">Are you sure you want to permanently delete</p>
            <p class="text-sm font-bold text-slate-800">"{{ $shop->name }}"?</p>
            <p class="text-xs text-slate-400 mt-2">This action cannot be undone. All pricing, earnings, and withdrawal data will be lost.</p>
        </div>
        <div class="px-6 pb-6 flex gap-3">
            <button onclick="document.getElementById('deleteShopModal').classList.add('hidden')"
                class="flex-1 px-4 py-2.5 text-sm font-bold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Cancel
            </button>
            <form method="POST" action="{{ route('admin.shops.destroy', $shop->id) }}" class="flex-1" onsubmit="return confirm('This will permanently delete the shop and all associated data. Continue?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full px-4 py-2.5 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-xl transition shadow-sm shadow-red-500/20">
                    Yes, Delete Shop
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Edit Pricing Modal --}}
<div id="editPricingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl shadow-slate-200/50 w-full max-w-md animate-fade-in">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Edit Pricing</h3>
            <button onclick="document.getElementById('editPricingModal').classList.add('hidden')"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <form id="editPricingForm" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-slate-500 mb-3" id="pricingInfo"></p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Base Price (GH&#8373;)</label>
                    <input type="text" id="editBasePrice" readonly
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Selling Price (GH&#8373;)</label>
                    <input type="number" name="selling_price" id="editSellingPrice" step="0.01" min="0" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 transition"
                        placeholder="Enter selling price">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Profit</label>
                    <input type="text" id="editProfit" readonly
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="document.getElementById('editPricingModal').classList.add('hidden')"
                    class="flex-1 px-4 py-2.5 text-sm font-bold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-[#2563EB] hover:bg-[#1D4ED8] rounded-xl transition shadow-sm shadow-blue-500/20">
                    Update Price
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Reject Withdrawal Modal --}}
<div id="rejectWithdrawalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl shadow-slate-200/50 w-full max-w-md animate-fade-in">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Reject Withdrawal</h3>
            <button onclick="document.getElementById('rejectWithdrawalModal').classList.add('hidden')"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <form id="rejectForm" method="POST" class="p-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Admin Note (optional)</label>
                    <textarea name="admin_note" rows="3"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 transition resize-none"
                        placeholder="Reason for rejection..."></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="document.getElementById('rejectWithdrawalModal').classList.add('hidden')"
                    class="flex-1 px-4 py-2.5 text-sm font-bold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-xl transition shadow-sm shadow-red-500/20">
                    Reject Withdrawal
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Tab switching
    function switchTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('text-[#2563EB]', 'border-[#2563EB]');
            el.classList.add('text-slate-400', 'border-transparent');
        });

        document.getElementById('tab-' + tab).classList.remove('hidden');
        const activeBtn = document.querySelector('[data-tab="' + tab + '"]');
        activeBtn.classList.remove('text-slate-400', 'border-transparent');
        activeBtn.classList.add('text-[#2563EB]', 'border-[#2563EB]');
    }

    // Initialize default tab
    document.addEventListener('DOMContentLoaded', function() {
        switchTab('pricing');
    });

    // Edit Pricing Modal
    let currentEditBasePrice = 0;
    function openEditPricingModal(id, network, packageSize, basePrice, sellingPrice) {
        document.getElementById('editPricingForm').action = '{{ url(config("app.admin_path") . "/shop-pricing") }}/' + id;
        document.getElementById('pricingInfo').textContent = network + ' — ' + packageSize;
        document.getElementById('editBasePrice').value = 'GH\u20B5' + parseFloat(basePrice).toFixed(2);
        document.getElementById('editSellingPrice').value = sellingPrice;
        currentEditBasePrice = parseFloat(basePrice);
        updateProfit();
        document.getElementById('editPricingModal').classList.remove('hidden');
    }

    document.getElementById('editSellingPrice').addEventListener('input', updateProfit);

    function updateProfit() {
        const selling = parseFloat(document.getElementById('editSellingPrice').value) || 0;
        const profit = selling - currentEditBasePrice;
        const profitInput = document.getElementById('editProfit');
        profitInput.value = 'GH\u20B5' + profit.toFixed(2);
        profitInput.className = 'w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm cursor-not-allowed font-bold ' +
            (profit >= 0 ? 'text-emerald-600' : 'text-red-500');
    }

    // Reject Withdrawal Modal
    function openRejectModal(withdrawalId) {
        document.getElementById('rejectForm').action = '{{ url(config("app.admin_path") . "/shop-withdrawals") }}/' + withdrawalId + '/reject';
        document.getElementById('rejectWithdrawalModal').classList.remove('hidden');
    }

    // Close modals on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('deleteShopModal').classList.add('hidden');
            document.getElementById('editPricingModal').classList.add('hidden');
            document.getElementById('rejectWithdrawalModal').classList.add('hidden');
        }
    });

    // Close modals on backdrop click
    ['deleteShopModal', 'editPricingModal', 'rejectWithdrawalModal'].forEach(function(id) {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
    });
</script>
@endpush
