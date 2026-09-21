@extends('layouts.user')
@section('title', 'Manage Pricing')
@section('page-title', 'Manage Pricing')
@section('page-description', 'Add packages to your shop and set your selling prices')

@section('content')
@php
    $totalPackages  = $pricing->count();
    $avgMargin      = $totalPackages > 0 ? $pricing->avg('profit') : 0;
    $canAddMore     = $availablePackages->count() > 0;
    $availableByNetwork = $availablePackages->groupBy('network_type')->sortKeys();
    $pricingByNetwork = $pricing->groupBy('network_type')->sortKeys();
@endphp

{{-- Flash Messages --}}
@if(session('success'))
    <div id="flash-success" class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200/60 text-emerald-700 text-sm font-medium px-4 py-3 rounded-xl shadow-sm">
        <x-heroicon-o-check-circle class="w-5 h-5 shrink-0 text-emerald-500" />
        {{ session('success') }}
        <button onclick="document.getElementById('flash-success').remove()" class="ml-auto text-emerald-400 hover:text-emerald-600 transition"><x-heroicon-o-x-mark class="w-4 h-4" /></button>
    </div>
@endif
@if(session('error'))
    <div id="flash-error" class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200/60 text-red-700 text-sm font-medium px-4 py-3 rounded-xl shadow-sm">
        <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0 text-red-500" />
        {{ session('error') }}
        <button onclick="document.getElementById('flash-error').remove()" class="ml-auto text-red-400 hover:text-red-600 transition"><x-heroicon-o-x-mark class="w-4 h-4" /></button>
    </div>
@endif
@if(session('info'))
    <div id="flash-info" class="mb-5 flex items-center gap-3 bg-blue-50 border border-blue-200/60 text-blue-700 text-sm font-medium px-4 py-3 rounded-xl shadow-sm">
        <x-heroicon-o-information-circle class="w-5 h-5 shrink-0 text-blue-500" />
        {{ session('info') }}
        <button onclick="document.getElementById('flash-info').remove()" class="ml-auto text-blue-400 hover:text-blue-600 transition"><x-heroicon-o-x-mark class="w-4 h-4" /></button>
    </div>
@endif

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <a href="{{ route('user.shop.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400 hover:text-[#EA580C] transition mb-2">
            <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
            Back to Shop
        </a>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Manage Pricing</h1>
        <p class="text-sm text-slate-400 mt-0.5">Add packages, set your selling prices, and manage your catalogue</p>
    </div>
    @if($canAddMore)
    <button
        id="openAddPackageBtn"
        onclick="openAddPackageModal()"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-sm shadow-orange-500/20 shrink-0"
    >
        <x-heroicon-o-plus class="w-4 h-4" />
        Add Package
    </button>
    @endif
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-moving-gradient text-white rounded-2xl shadow-sm p-5 flex items-center gap-4 border-0">
        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
            <x-heroicon-o-cube class="w-5 h-5 text-white" />
        </div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-white/80">Active Packages</p>
            <p class="text-2xl font-black text-white">{{ $totalPackages }}</p>
        </div>
    </div>
    <div class="bg-moving-gradient text-white rounded-2xl shadow-sm p-5 flex items-center gap-4 border-0">
        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
            <x-heroicon-o-chart-bar class="w-5 h-5 text-white" />
        </div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-white/80">Avg. Profit Margin</p>
            <p class="text-2xl font-black text-white">GH&#8373;{{ number_format($avgMargin, 2) }}</p>
        </div>
    </div>
    <div class="bg-moving-gradient text-white rounded-2xl shadow-sm p-5 flex items-center gap-4 border-0">
        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
            <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-white" />
        </div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-white/80">Available to Add</p>
            <p class="text-2xl font-black text-white">{{ $availablePackages->count() }}</p>
        </div>
    </div>
</div>

{{-- Pricing Table --}}
<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-[#EA580C]/10 flex items-center justify-center">
                <x-heroicon-o-tag class="w-4 h-4 text-[#EA580C]" />
            </div>
            <h3 class="text-sm font-bold text-slate-800">Your Packages</h3>
        </div>
        <span class="text-xs text-slate-400 font-medium">{{ $totalPackages }} {{ Str::plural('package', $totalPackages) }}</span>
    </div>

    @if($pricing->isEmpty())
        <div class="py-20 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <x-heroicon-o-cube class="w-8 h-8 text-slate-300" />
            </div>
            <p class="text-sm font-semibold text-slate-500 mb-1">No packages yet</p>
            <p class="text-xs text-slate-400 mb-5">Add packages from the admin catalogue to start selling.</p>
            @if($canAddMore)
            <button onclick="openAddPackageModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-semibold rounded-xl transition-all duration-200">
                <x-heroicon-o-plus class="w-4 h-4" />
                Add Your First Package
            </button>
            @endif
        </div>
    @else
        {{-- Network Filter Tabs --}}
        @php $networks = $pricingByNetwork->keys(); @endphp
        @if($networks->count() > 1)
        <div class="px-6 pt-4 flex items-center gap-2 flex-wrap">
            <button onclick="filterNetwork('all')" data-net="all"
                class="net-tab px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-[#EA580C]/10 text-[#EA580C]">
                All
            </button>
            @foreach($networks as $net)
            <button onclick="filterNetwork('{{ $net }}')" data-net="{{ $net }}"
                class="net-tab px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-500 hover:bg-slate-100">
                {{ strtoupper($net) }}
            </button>
            @endforeach
        </div>
        @endif

        <div class="overflow-x-auto mt-2">
            <table class="w-full text-sm" id="pricingTable">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Network</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Package</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Base Price</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Your Price</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Profit</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Margin</th>
                        <th class="text-right px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80" id="pricingBody">
                    @foreach($pricing->sortBy('network_type') as $item)
                    @php
                        $marginPct = $item->base_price > 0
                            ? (($item->selling_price - $item->base_price) / $item->base_price) * 100
                            : 0;
                    @endphp
                    <tr class="hover:bg-orange-50/20 transition pricing-row" data-network="{{ $item->network_type }}">
                        <td class="px-6 py-4">
                            <x-network-badge :network="$item->network_type" />
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-700">{{ $item->package_size }}</td>
                        <td class="px-6 py-4 text-sm text-slate-400">GH&#8373;{{ number_format($item->base_price, 2) }}</td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('user.shop.pricing.update', $item->id) }}" class="flex items-center gap-2" id="form-{{ $item->id }}">
                                @csrf
                                @method('PUT')
                                <div class="relative">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">GH&#8373;</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="{{ $item->base_price }}"
                                        name="selling_price"
                                        value="{{ $item->selling_price }}"
                                        data-base="{{ $item->base_price }}"
                                        data-row="{{ $item->id }}"
                                        oninput="updateRowProfit(this)"
                                        class="w-28 pl-9 pr-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all"
                                        required
                                    >
                                </div>
                                <button type="submit" title="Save"
                                    class="p-1.5 text-emerald-500 hover:bg-emerald-50 rounded-lg transition-colors flex items-center justify-center">
                                    <x-heroicon-o-check class="w-4 h-4" />
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            <span id="profit-{{ $item->id }}" class="text-sm font-semibold {{ $item->profit > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                GH&#8373;{{ number_format($item->profit, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span id="margin-{{ $item->id }}" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $marginPct >= 5 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : ($marginPct > 0 ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-slate-100 text-slate-400 border border-slate-200') }}">
                                {{ number_format($marginPct, 1) }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button
                                onclick="confirmRemove({{ $item->id }}, '{{ addslashes($item->network_type) }}', '{{ addslashes($item->package_size) }}')"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50 rounded-lg border border-transparent hover:border-red-100 transition-all"
                                title="Remove from shop"
                            >
                                <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                Remove
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Tip --}}
<div class="bg-gradient-to-r from-orange-50/80 to-amber-50/50 border border-orange-100/60 rounded-2xl p-5 flex items-start gap-3.5">
    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center mt-0.5">
        <x-heroicon-o-information-circle class="w-5 h-5 text-[#EA580C]" />
    </div>
    <div>
        <p class="text-sm font-semibold text-slate-700">Pricing Tips</p>
        <ul class="mt-1.5 space-y-1">
            <li class="text-xs text-slate-500 flex items-start gap-1.5"><span class="text-[#EA580C] font-bold mt-0.5">&bull;</span> Your selling price must always be &ge; the base price to earn profit.</li>
            <li class="text-xs text-slate-500 flex items-start gap-1.5"><span class="text-[#EA580C] font-bold mt-0.5">&bull;</span> Competitive margins are typically 5&ndash;15%. A 0% margin means no profit.</li>
            <li class="text-xs text-slate-500 flex items-start gap-1.5"><span class="text-[#EA580C] font-bold mt-0.5">&bull;</span> You can remove any package from your shop at any time &mdash; it won&apos;t affect existing orders.</li>
        </ul>
    </div>
</div>

{{-- ADD PACKAGE MODAL --}}
<div id="addPackageModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-[#EA580C]/10 flex items-center justify-center">
                    <x-heroicon-o-plus class="w-4 h-4 text-[#EA580C]" />
                </div>
                <h3 class="text-sm font-bold text-slate-800">Add Package to Shop</h3>
            </div>
            <button onclick="closeAddPackageModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>

        <form method="POST" action="{{ route('user.shop.pricing.add') }}" id="addPackageForm">
            @csrf
            <div class="p-6 space-y-5">

                {{-- Network Filter --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Filter by Network</label>
                    <div class="flex flex-wrap gap-2" id="networkFilterBtns">
                        <button type="button" onclick="filterModalNetwork('all')" data-filter="all"
                            class="modal-net-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#EA580C]/10 text-[#EA580C] transition-all">
                            All Networks
                        </button>
                        @foreach($availableByNetwork->keys() as $net)
                        <button type="button" onclick="filterModalNetwork('{{ $net }}')" data-filter="{{ $net }}"
                            class="modal-net-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 hover:bg-slate-100 transition-all">
                            {{ strtoupper($net) }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Package Select --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Select Package</label>
                    <select name="custom_pricing_id" id="packageSelect" required onchange="onPackageSelect(this)"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Choose a package...</option>
                        @foreach($availablePackages as $pkg)
                        <option
                            value="{{ $pkg->id }}"
                            data-network="{{ $pkg->network_type }}"
                            data-base="{{ $pkg->cost }}"
                            data-size="{{ $pkg->package_size }}"
                        >
                            {{ strtoupper($pkg->network_type) }} &mdash; {{ $pkg->package_size }} (Base: GH&#8373;{{ number_format($pkg->cost, 2) }})
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Base Price Preview --}}
                <div id="basePriceRow" class="hidden">
                    <div class="flex items-center justify-between p-3.5 bg-slate-50 border border-slate-200/60 rounded-xl">
                        <span class="text-xs font-semibold text-slate-500">Base Price <span class="text-slate-400 font-normal">(admin set, fixed)</span></span>
                        <span id="modalBasePrice" class="text-sm font-black text-slate-700 tabular-nums">GH&#8373;0.00</span>
                    </div>
                </div>

                {{-- Selling Price --}}
                <div id="sellingPriceRow" class="hidden">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Your Selling Price</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none font-semibold">GH&#8373;</span>
                        <input
                            type="number"
                            name="selling_price"
                            id="modalSellingPrice"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            oninput="updateModalProfit()"
                            class="w-full pl-12 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all"
                        >
                    </div>
                    <p id="modalPriceHint" class="text-[11px] text-slate-400 mt-1.5"></p>
                </div>

                {{-- Profit Preview --}}
                <div id="profitPreviewRow" class="hidden">
                    <div class="flex items-center gap-3 p-3.5 rounded-xl border bg-slate-50 border-slate-200/60" id="profitPreviewBox">
                        <x-heroicon-o-banknotes class="w-4 h-4 shrink-0 text-slate-400" id="profitIcon" />
                        <div class="flex-1 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500">Estimated Profit per Sale</span>
                            <span id="modalProfit" class="text-sm font-black tabular-nums text-slate-600">GH&#8373;0.00</span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="px-6 pb-6 flex gap-3">
                <button type="button" onclick="closeAddPackageModal()"
                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" id="addPackageSubmitBtn" disabled
                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-[#EA580C] hover:bg-[#C2410C] disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed rounded-xl transition-all shadow-sm shadow-orange-500/15">
                    Add to Shop
                </button>
            </div>
        </form>
    </div>
</div>

{{-- REMOVE CONFIRM MODAL --}}
<div id="removeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-sm">
        <div class="p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                <x-heroicon-o-trash class="w-7 h-7 text-red-500" />
            </div>
            <h3 class="text-base font-black text-slate-800 mb-1">Remove Package?</h3>
            <p class="text-sm text-slate-500 mb-0.5">You are about to remove</p>
            <p id="removePackageName" class="text-sm font-bold text-slate-800 mb-1">&mdash;</p>
            <p class="text-xs text-slate-400">from your shop. Existing orders won&apos;t be affected.</p>
        </div>
        <div class="px-6 pb-6 flex gap-3">
            <button onclick="closeRemoveModal()" class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Cancel
            </button>
            <form id="removeForm" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 rounded-xl transition shadow-sm shadow-red-500/20">
                    Yes, Remove
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
var modalBasePrice = 0;

function openAddPackageModal() {
    document.getElementById('addPackageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAddPackageModal() {
    document.getElementById('addPackageModal').classList.add('hidden');
    document.body.style.overflow = '';
    document.getElementById('addPackageForm').reset();
    resetModalState();
}

function resetModalState() {
    modalBasePrice = 0;
    document.getElementById('basePriceRow').classList.add('hidden');
    document.getElementById('sellingPriceRow').classList.add('hidden');
    document.getElementById('profitPreviewRow').classList.add('hidden');
    document.getElementById('addPackageSubmitBtn').disabled = true;
}

function onPackageSelect(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (!opt.value) { resetModalState(); return; }

    modalBasePrice = parseFloat(opt.getAttribute('data-base')) || 0;

    document.getElementById('modalBasePrice').textContent = 'GH\u20B5' + modalBasePrice.toFixed(2);
    document.getElementById('basePriceRow').classList.remove('hidden');

    var spInput = document.getElementById('modalSellingPrice');
    spInput.min = modalBasePrice;
    spInput.value = modalBasePrice.toFixed(2);

    document.getElementById('modalPriceHint').textContent = 'Minimum: GH\u20B5' + modalBasePrice.toFixed(2) + ' (base price)';
    document.getElementById('sellingPriceRow').classList.remove('hidden');
    document.getElementById('profitPreviewRow').classList.remove('hidden');

    updateModalProfit();
}

function updateModalProfit() {
    var spVal = parseFloat(document.getElementById('modalSellingPrice').value) || 0;
    var profit = spVal - modalBasePrice;
    var profitEl = document.getElementById('modalProfit');
    var boxEl = document.getElementById('profitPreviewBox');
    var submitBtn = document.getElementById('addPackageSubmitBtn');

    profitEl.textContent = 'GH\u20B5' + profit.toFixed(2);

    if (profit < 0) {
        boxEl.className = 'flex items-center gap-3 p-3.5 rounded-xl border bg-red-50 border-red-200/60';
        profitEl.className = 'text-sm font-black tabular-nums text-red-600';
        document.getElementById('modalPriceHint').textContent = 'Price is below base price — must be at least GH\u20B5' + modalBasePrice.toFixed(2);
        submitBtn.disabled = true;
    } else if (profit === 0) {
        boxEl.className = 'flex items-center gap-3 p-3.5 rounded-xl border bg-amber-50 border-amber-200/60';
        profitEl.className = 'text-sm font-black tabular-nums text-amber-600';
        document.getElementById('modalPriceHint').textContent = 'No markup — you won\'t earn profit on this package.';
        submitBtn.disabled = false;
    } else {
        boxEl.className = 'flex items-center gap-3 p-3.5 rounded-xl border bg-emerald-50 border-emerald-200/60';
        profitEl.className = 'text-sm font-black tabular-nums text-emerald-600';
        document.getElementById('modalPriceHint').textContent = 'You\'ll earn GH\u20B5' + profit.toFixed(2) + ' per sale.';
        submitBtn.disabled = false;
    }
}

function filterModalNetwork(net) {
    document.querySelectorAll('.modal-net-btn').forEach(function(btn) {
        if (btn.getAttribute('data-filter') === net) {
            btn.className = 'modal-net-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#EA580C]/10 text-[#EA580C] transition-all';
        } else {
            btn.className = 'modal-net-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 hover:bg-slate-100 transition-all';
        }
    });

    var sel = document.getElementById('packageSelect');
    var opts = sel.querySelectorAll('option');
    opts.forEach(function(opt) {
        if (!opt.value) return;
        var show = net === 'all' || opt.getAttribute('data-network') === net;
        opt.style.display = show ? '' : 'none';
    });

    sel.value = '';
    resetModalState();
}

function filterNetwork(net) {
    document.querySelectorAll('.net-tab').forEach(function(btn) {
        if (btn.getAttribute('data-net') === net) {
            btn.className = 'net-tab px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-[#EA580C]/10 text-[#EA580C]';
        } else {
            btn.className = 'net-tab px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-500 hover:bg-slate-100';
        }
    });

    document.querySelectorAll('.pricing-row').forEach(function(row) {
        row.style.display = (net === 'all' || row.getAttribute('data-network') === net) ? '' : 'none';
    });
}

function updateRowProfit(input) {
    var base = parseFloat(input.getAttribute('data-base')) || 0;
    var sell = parseFloat(input.value) || 0;
    var profit = sell - base;
    var id = input.getAttribute('data-row');
    var marginPct = base > 0 ? (profit / base) * 100 : 0;

    var profitEl = document.getElementById('profit-' + id);
    var marginEl = document.getElementById('margin-' + id);

    if (profitEl) {
        profitEl.textContent = 'GH\u20B5' + profit.toFixed(2);
        profitEl.className = profit > 0
            ? 'text-sm font-semibold text-emerald-600'
            : (profit === 0 ? 'text-sm font-semibold text-amber-500' : 'text-sm font-semibold text-red-500');
    }

    if (marginEl) {
        marginEl.textContent = marginPct.toFixed(1) + '%';
        if (marginPct >= 5) {
            marginEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100';
        } else if (marginPct > 0) {
            marginEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-600 border border-amber-100';
        } else {
            marginEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-400 border border-slate-200';
        }
    }
}

function confirmRemove(id, network, packageSize) {
    document.getElementById('removePackageName').textContent = network.toUpperCase() + ' \u2014 ' + packageSize;
    document.getElementById('removeForm').action = '/user/shop/pricing/' + id;
    document.getElementById('removeModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRemoveModal() {
    document.getElementById('removeModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddPackageModal();
        closeRemoveModal();
    }
});

document.getElementById('addPackageModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddPackageModal();
});
document.getElementById('removeModal').addEventListener('click', function(e) {
    if (e.target === this) closeRemoveModal();
});
</script>
@endpush
@endsection
