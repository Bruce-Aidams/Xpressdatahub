@extends('layouts.user')
@section('title', 'Buy Data')
@section('page-title', 'Buy Data')
@section('page-description', 'Purchase data bundles instantly')
@section('content')

@php
    $availableNetworks = $pricing->keys()->toArray();
    $allNetworks = ['MTN', 'Telecel', 'AirtelTigo'];
    $pricingJson = [];
    foreach ($pricing as $network => $packages) {
        $pricingJson[$network] = $packages->map(fn($p) => [
            'size'  => $p->package_size,
            'price' => (float) $p->selling_price,
        ])->values();
    }
@endphp

{{-- Flash Messages --}}
@if(session('success'))
<div class="mb-5 flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-sm text-emerald-800 font-medium">
    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-800 font-medium">
    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('error') }}
</div>
@endif

<div class="max-w-xl mx-auto">

    {{-- ── MODE TABS ──────────────────────────────────────────── --}}
    @unless($isGuest ?? false)
    <div class="flex gap-1 p-1 bg-slate-100 rounded-xl mb-6" id="modeTabs">
        <button type="button" data-mode="single"
            class="mode-tab flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-semibold transition-all bg-white text-slate-900 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Single
        </button>
        <button type="button" data-mode="bulk"
            class="mode-tab flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-semibold transition-all text-slate-500 hover:text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/></svg>
            Bulk
        </button>
    </div>
    @endunless

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- SINGLE ORDER PANEL                                        --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div id="singleMode">
        <form id="singleOrderForm" onsubmit="event.preventDefault(); showConfirmModal();" autocomplete="off" class="space-y-3">

            {{-- ── 1. NETWORK ────────────────────────────────────── --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Network</label>
                <div class="grid grid-cols-3 gap-2" id="networkCards">
                    @php
                        $netMeta = [
                            'MTN'        => ['initials'=>'MT','dot'=>'bg-amber-400','activeBg'=>'bg-gradient-to-br from-amber-400 to-yellow-500','activeBorder'=>'border-amber-400','activeText'=>'text-amber-900','iconBg'=>'bg-amber-100','iconColor'=>'text-amber-600','hoverBorder'=>'hover:border-amber-300 hover:bg-amber-50'],
                            'Telecel'    => ['initials'=>'TC','dot'=>'bg-red-500','activeBg'=>'bg-gradient-to-br from-red-500 to-rose-600','activeBorder'=>'border-red-500','activeText'=>'text-red-900','iconBg'=>'bg-red-100','iconColor'=>'text-red-600','hoverBorder'=>'hover:border-red-300 hover:bg-red-50'],
                            'AirtelTigo' => ['initials'=>'AT','dot'=>'bg-blue-500','activeBg'=>'bg-gradient-to-br from-blue-500 to-indigo-600','activeBorder'=>'border-blue-500','activeText'=>'text-blue-900','iconBg'=>'bg-blue-100','iconColor'=>'text-blue-600','hoverBorder'=>'hover:border-blue-300 hover:bg-blue-50'],
                        ];
                    @endphp
                    @foreach($allNetworks as $network)
                        @php
                            $hasPackages = in_array($network, $availableNetworks);
                            $isFirst     = $loop->first && $hasPackages;
                            $meta        = $netMeta[$network];
                        @endphp
                        <button type="button"
                            class="network-card relative flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-2xl border-2 transition-all duration-150 text-center
                                {{ $isFirst
                                    ? $meta['activeBg'].' '.$meta['activeBorder'].' text-white shadow-lg scale-[1.02]'
                                    : ($hasPackages
                                        ? 'border-slate-200 bg-white text-slate-700 '.$meta['hoverBorder']
                                        : 'border-slate-100 bg-slate-50 text-slate-300 cursor-not-allowed') }}"
                            data-network="{{ $network }}"
                            data-available="{{ $hasPackages ? 'true' : 'false' }}"
                            data-active-bg="{{ $meta['activeBg'] }}"
                            data-active-border="{{ $meta['activeBorder'] }}"
                            data-hover-border="{{ $meta['hoverBorder'] }}"
                            data-icon-color="{{ $meta['iconColor'] }}"
                            @if(!$hasPackages) disabled @endif>
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0
                                {{ $isFirst ? 'bg-white/20' : $meta['iconBg'] }} {{ !$hasPackages ? 'opacity-30' : '' }}">
                                <span class="text-xs font-black {{ $isFirst ? 'text-white' : $meta['iconColor'] }}">{{ $meta['initials'] }}</span>
                            </span>
                            <span class="text-xs font-bold leading-none">{{ $network }}</span>
                            @if($isFirst)
                            <span class="absolute top-1.5 right-1.5 w-3.5 h-3.5 bg-white/30 rounded-full flex items-center justify-center active-check">
                                <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
                            </span>
                            @endif
                            @if(!$hasPackages)
                                <span class="absolute top-1.5 right-1.5 text-[9px] font-bold text-slate-300 uppercase">N/A</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- ── 2. PACKAGE ────────────────────────────────────── --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3" for="packageSelect">Package</label>
                <select id="packageSelect"
                    class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:border-slate-400 focus:bg-white transition appearance-none cursor-pointer">
                    <option value="" disabled selected>Select a network first</option>
                </select>
                <input type="hidden" id="selectedPackageSize">
                <input type="hidden" id="selectedPackagePrice">

                {{-- Selected package summary strip --}}
                <div id="selectedPackageBadge" class="hidden mt-3 flex items-center justify-between px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/></svg>
                        <span class="text-sm font-bold text-slate-800" id="selectedPackageLabel">—</span>
                    </div>
                    <span class="text-sm font-black text-slate-900" id="selectedPackagePrice2">—</span>
                </div>
            </div>

            {{-- ── 3. RECIPIENT ──────────────────────────────────── --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Recipient Number</label>
                    <span id="phoneStatus" class="text-[10px] font-semibold text-slate-300 tabular-nums">0 / 10</span>
                </div>
                <div class="relative">
                    <input
                        type="tel"
                        id="phoneNumber"
                        inputmode="numeric"
                        placeholder="0241234567"
                        maxlength="10"
                        autocomplete="off"
                        class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-[15px] font-semibold text-slate-900 placeholder-slate-300 focus:outline-none focus:border-slate-400 focus:bg-white transition tracking-[0.08em]"
                        required>
                    <div id="phoneCheckIcon" class="hidden absolute right-3.5 top-1/2 -translate-y-1/2">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>
                </div>
                <p id="phoneError" class="hidden mt-2 flex items-center gap-1.5 text-xs font-medium text-red-500">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span id="phoneErrorText">Invalid number</span>
                </p>
            </div>

            {{-- ── 4. PAYMENT ────────────────────────────────────── --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Payment</label>

                @if($isGuest ?? false)
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all border-violet-500 bg-gradient-to-br from-violet-500 to-purple-600" id="method-paystack-label">
                            <input type="radio" name="guest_payment_method" value="paystack" class="sr-only peer" checked onchange="toggleGuestPayment()">
                            <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white">Paystack</p>
                                <p class="text-[10px] text-white/60">Card / Bank</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 hover:border-amber-400" id="method-momo-label">
                            <input type="radio" name="guest_payment_method" value="manual_momo" class="sr-only peer" onchange="toggleGuestPayment()">
                            <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-amber-700">MoMo Pay</p>
                                <p class="text-[10px] text-amber-500">Manual Transfer</p>
                            </div>
                        </label>
                    </div>

                    <div id="momoDetailsSection" class="hidden space-y-3">
                        <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200">
                            <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider mb-1">Pay To</p>
                            <p class="text-sm font-black text-slate-800">{{ $momoName ?? 'Admin' }}</p>
                            <p class="text-lg font-mono font-bold text-amber-700">{{ $momoNumber ?? 'Not Configured' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Your MoMo Account Name</label>
                            <input type="text" id="momoSenderName" placeholder="e.g. John Doe"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-amber-400 focus:bg-white transition">
                        </div>
                    </div>

                @else
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">Wallet</p>
                                <p class="text-[11px] text-white/70">GH&#8373;{{ number_format($agent->balance ?? 0, 2) }} available</p>
                            </div>
                        </div>
                        <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                    </div>
                    <div id="insufficientBalanceMsg" class="hidden mt-2 flex items-center gap-2 p-3 bg-red-50 border border-red-100 rounded-xl text-xs text-red-700">
                        <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Insufficient balance. <a href="{{ route('user.wallet.topup') }}" class="font-bold text-red-600 hover:underline">Top up &rarr;</a></span>
                    </div>
                @endif
            </div>

            {{-- ── SUBMIT ─────────────────────────────────────────── --}}
            <button type="submit" id="createOrderBtn" disabled
                class="w-full py-3.5 bg-slate-200 disabled:bg-slate-100 disabled:text-slate-300 disabled:cursor-not-allowed text-white font-bold text-sm rounded-2xl transition-all duration-150 flex items-center justify-center gap-2 active:scale-[0.99]">
                Continue to Review
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>
    </div>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- BULK ORDER PANEL                                          --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div id="bulkMode" class="hidden space-y-3">
        <form method="POST" action="{{ route('user.bulk-orders.store') }}" id="bulkForm">
            @csrf

            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Paste Orders</h3>
                    <p class="text-xs text-slate-400 mt-0.5">One per line — format: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-[11px] font-mono">phone,package</code></p>
                </div>
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Available Packages</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($pricing as $network => $packages)
                            @foreach($packages as $pkg)
                                <button type="button" data-pkg="{{ $pkg->package_size }}"
                                    class="pkg-chip px-2 py-0.5 rounded-md border text-[11px] font-semibold transition-all
                                        {{ $network === 'MTN' ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : ($network === 'Telecel' ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100') }}">
                                    {{ $pkg->package_size }} <span class="opacity-60">GH&#8373;{{ number_format($pkg->selling_price, 2) }}</span>
                                </button>
                            @endforeach
                        @endforeach
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    @php $firstPkg = $pricing->first()?->first()?->package_size ?? '1GB'; @endphp
                    <textarea id="bulkInput" rows="5"
                        placeholder="0241234567,{{ $firstPkg }}&#10;0551234567,{{ $firstPkg }}"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:outline-none focus:border-slate-400 focus:bg-white transition leading-relaxed resize-y"></textarea>
                    <div id="bulkErrors" class="hidden space-y-1.5"></div>
                    <button type="button" id="bulkParseBtn"
                        class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Preview Orders
                    </button>
                </div>
            </div>

            {{-- Preview Table --}}
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden flex flex-col" style="max-height: 50vh;">
                <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <span class="text-sm font-bold text-slate-900">Preview</span>
                    <span id="rowCount" class="text-xs font-bold text-slate-400">0 orders</span>
                </div>
                <div id="rowsContainer" class="flex-1 overflow-y-auto divide-y divide-slate-100">
                    <div id="emptyState" class="flex flex-col items-center justify-center py-10 text-slate-300">
                        <svg class="w-8 h-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-xs">No orders yet</p>
                    </div>
                </div>
                <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 shrink-0">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs text-slate-500 font-medium">Total</span>
                        <span class="text-xl font-black text-slate-900" id="bulkTotal">GH&#8373;0.00</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" id="bulkSubmitBtn" disabled
                            class="flex-1 py-3 bg-slate-900 hover:bg-slate-800 disabled:bg-slate-100 disabled:text-slate-300 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl transition-all flex items-center justify-center gap-2">
                            Place Orders
                        </button>
                        <button type="button" id="bulkClearBtn"
                            class="px-5 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-sm rounded-xl transition-all">
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- REVIEW MODAL                                                  --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div id="confirmModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
    <div class="absolute inset-0 bg-black/40" onclick="closeConfirmModal()"></div>
    <div id="confirmPanel" class="relative w-full sm:max-w-sm bg-white sm:rounded-3xl rounded-t-3xl shadow-2xl translate-y-8 sm:scale-95 transition-all duration-200 overflow-hidden">

        {{-- Colored network header bar --}}
        <div id="modalHeaderBar" class="h-1.5 w-full bg-slate-200 transition-all duration-300"></div>

        {{-- Drag handle (mobile) --}}
        <div class="flex justify-center pt-3 pb-1 sm:hidden">
            <div class="w-10 h-1 rounded-full bg-slate-200"></div>
        </div>

        <div class="px-6 pt-4 pb-2 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div id="modalNetworkDot" class="w-3 h-3 rounded-full bg-slate-300 transition-colors duration-300"></div>
                <h3 class="text-base font-black text-slate-900">Review Order</h3>
            </div>
            <button onclick="closeConfirmModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="px-6 pb-6 space-y-4 mt-2">
            {{-- Summary rows --}}
            <div class="rounded-2xl border border-slate-100 overflow-hidden divide-y divide-slate-100">
                <div id="modalNetworkRow" class="flex justify-between items-center px-4 py-3">
                    <span class="text-sm text-slate-500">Network</span>
                    <span id="summaryNetwork" class="text-sm font-bold text-slate-900">—</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-sm text-slate-500">Package</span>
                    <span id="summaryPackage" class="text-sm font-bold text-slate-900">—</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-sm text-slate-500">To</span>
                    <span id="summaryPhone" class="text-sm font-bold font-mono text-slate-900 tracking-widest">—</span>
                </div>
                <div id="modalAmountRow" class="flex justify-between items-center px-4 py-3.5 bg-slate-50 transition-colors duration-300">
                    <span class="text-sm font-semibold text-slate-700">Amount</span>
                    <span id="summaryPrice" class="text-xl font-black text-slate-900">GH&#8373;0.00</span>
                </div>
            </div>

            {{-- Confirm form --}}
            <form method="POST" action="{{ route('user.buy-data.store') }}" id="checkoutForm">
                @csrf
                <input type="hidden" name="network_type" id="formNetwork">
                <input type="hidden" name="package_size" id="formPackage">
                <input type="hidden" name="phone_number" id="formPhone">
                <input type="hidden" name="payment_method" id="formPaymentMethod" value="paystack">
                <input type="hidden" name="sender_name" id="formSenderName">

                <div class="space-y-2">
                    <button type="submit" id="checkoutSubmitBtn"
                        class="w-full py-3.5 bg-slate-900 hover:opacity-90 text-white font-bold text-sm rounded-2xl transition-all flex items-center justify-center gap-2 active:scale-[0.99]">
                        Confirm Order
                    </button>
                    <button type="button" onclick="closeConfirmModal()"
                        class="w-full py-3 text-slate-500 hover:text-slate-700 font-semibold text-sm rounded-2xl transition-all">
                        Go back
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    var pricingData   = @json($pricingJson);
    var walletBalance = {{ ($isGuest ?? false) ? 999999 : (float)($agent->balance ?? 0) }};
    var isGuest       = {{ ($isGuest ?? false) ? 'true' : 'false' }};

    var networkPrefixes = {
        MTN:       ['024','025','053','054','055','059'],
        Telecel:   ['020','050'],
        AirtelTigo:['027','057','026','056','023']
    };

    var networkColors = {
        MTN:        { gradient: 'linear-gradient(135deg,#f59e0b,#eab308)', dot: '#f59e0b', btn: 'linear-gradient(135deg,#f59e0b,#eab308)', text: '#78350f' },
        Telecel:    { gradient: 'linear-gradient(135deg,#ef4444,#e11d48)', dot: '#ef4444', btn: 'linear-gradient(135deg,#ef4444,#e11d48)', text: '#7f1d1d' },
        AirtelTigo: { gradient: 'linear-gradient(135deg,#3b82f6,#6366f1)', dot: '#3b82f6', btn: 'linear-gradient(135deg,#3b82f6,#6366f1)', text: '#1e3a8a' },
    };

    function applyNetworkColor(network) {
        var c = networkColors[network];
        if (!c) return;

        var createBtn = document.getElementById('createOrderBtn');
        if (createBtn && !createBtn.disabled) {
            createBtn.style.background = c.btn;
            createBtn.style.color = '#fff';
        }
    }

    function clearNetworkColor() {
        var createBtn = document.getElementById('createOrderBtn');
        if (createBtn) {
            createBtn.style.background = '';
            createBtn.style.color = '';
        }
    }

    // ── MODE TABS ──────────────────────────────────────────────
    document.querySelectorAll('.mode-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var mode = this.dataset.mode;
            document.querySelectorAll('.mode-tab').forEach(function(t) {
                t.className = 'mode-tab flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-semibold transition-all text-slate-500 hover:text-slate-700';
            });
            this.className = 'mode-tab flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-semibold transition-all bg-white text-slate-900 shadow-sm';
            document.getElementById('singleMode').classList.toggle('hidden', mode !== 'single');
            document.getElementById('bulkMode').classList.toggle('hidden', mode !== 'bulk');
        });
    });

    // ── NETWORK SELECTION ──────────────────────────────────────
    var selectedNetwork = null;
    var selectedPrice   = 0;
    var selectedSize    = '';

    var firstAvail = document.querySelector('.network-card[data-available="true"]');
    if (firstAvail) {
        selectedNetwork = firstAvail.dataset.network;
        renderPackages(selectedNetwork);
    }

    document.querySelectorAll('.network-card').forEach(function(card) {
        card.addEventListener('click', function() {
            if (this.dataset.available !== 'true') return;
            var net = this.dataset.network;

            // Deselect all
            document.querySelectorAll('.network-card').forEach(function(c) {
                if (c.dataset.available !== 'true') return;
                var hoverBorder = c.dataset.hoverBorder || 'hover:border-slate-300 hover:bg-slate-50';
                c.className = 'network-card relative flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-2xl border-2 transition-all duration-150 text-center border-slate-200 bg-white text-slate-700 ' + hoverBorder;
                c.style.transform = '';
                var iconSpan = c.querySelector('span > span');
                if (iconSpan) { iconSpan.className = iconSpan.className.replace('text-white', c.dataset.iconColor || 'text-slate-600'); }
                var b = c.querySelector('.active-check'); if (b) b.remove();
            });

            // Activate with network color
            var activeBg     = this.dataset.activeBg || 'bg-slate-900';
            var activeBorder = this.dataset.activeBorder || 'border-slate-900';
            this.className = 'network-card relative flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-2xl border-2 transition-all duration-150 text-center text-white shadow-lg scale-[1.02] ' + activeBg + ' ' + activeBorder;

            // Update inner icon bg to white/20
            var iconWrap = this.querySelector('span:first-child');
            if (iconWrap) {
                iconWrap.className = iconWrap.className.replace(/bg-\w+-\d+/, 'bg-white/20');
                var innerText = iconWrap.querySelector('span');
                if (innerText) innerText.className = innerText.className.replace(/text-\w+-\d+/, 'text-white');
            }

            this.insertAdjacentHTML('beforeend',
                '<span class="absolute top-1.5 right-1.5 w-3.5 h-3.5 bg-white/30 rounded-full flex items-center justify-center active-check">' +
                '<svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>' +
                '</span>');

            // Clear package selection
            selectedNetwork = net;
            selectedSize    = '';
            selectedPrice   = 0;
            document.getElementById('selectedPackageSize').value  = '';
            document.getElementById('selectedPackagePrice').value = '';
            document.getElementById('selectedPackageBadge').classList.add('hidden');
            document.getElementById('packageSelect').value = '';
            renderPackages(net);
            validate();
        });
    });

    // ── PACKAGE SELECT ──────────────────────────────────────
    function renderPackages(network) {
        var select = document.getElementById('packageSelect');
        var packages = pricingData[network] || [];
        select.innerHTML = '';

        if (!packages.length) {
            var opt = document.createElement('option');
            opt.value = '';
            opt.disabled = true;
            opt.selected = true;
            opt.textContent = 'No packages available';
            select.appendChild(opt);
            return;
        }

        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.disabled = true;
        placeholder.selected = true;
        placeholder.textContent = 'Select a package';
        select.appendChild(placeholder);

        packages.forEach(function(pkg) {
            var opt = document.createElement('option');
            opt.value = pkg.size;
            opt.dataset.price = pkg.price;
            opt.textContent = pkg.size + ' — GH₵' + parseFloat(pkg.price).toFixed(2);
            select.appendChild(opt);
        });
    }

    var packageSelect = document.getElementById('packageSelect');
    packageSelect.addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) return;
        selectedSize  = opt.value;
        selectedPrice = parseFloat(opt.dataset.price);
        document.getElementById('selectedPackageSize').value  = opt.value;
        document.getElementById('selectedPackagePrice').value = opt.dataset.price;
        document.getElementById('selectedPackageLabel').textContent  = opt.value + ' Bundle';
        document.getElementById('selectedPackagePrice2').textContent = 'GH\u20B5' + selectedPrice.toFixed(2);
        document.getElementById('selectedPackageBadge').classList.remove('hidden');
        validate();
    });

    // ── PHONE VALIDATION ──────────────────────────────────────
    var phoneInput   = document.getElementById('phoneNumber');
    var phoneStatus  = document.getElementById('phoneStatus');
    var phoneCheck   = document.getElementById('phoneCheckIcon');
    var phoneError   = document.getElementById('phoneError');
    var phoneErrText = document.getElementById('phoneErrorText');
    var phoneValid   = false;

    phoneInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g,'').slice(0,10);
        var n = this.value.length;
        phoneStatus.textContent = n + ' / 10';
        phoneStatus.className = 'text-[10px] font-semibold tabular-nums ' + (n===10 ? 'text-emerald-500' : 'text-slate-300');
        phoneCheck.classList.add('hidden');
        phoneError.classList.add('hidden');
        phoneValid = false;

        if (n === 10) {
            var pre = this.value.slice(0,3);
            if (!/^0[235]/.test(this.value)) {
                phoneErrText.textContent = 'Number prefix not valid';
                phoneError.classList.remove('hidden');
            } else if (selectedNetwork && networkPrefixes[selectedNetwork] && networkPrefixes[selectedNetwork].indexOf(pre) === -1) {
                phoneErrText.textContent = "Doesn't match " + selectedNetwork;
                phoneError.classList.remove('hidden');
            } else {
                phoneCheck.classList.remove('hidden');
                phoneValid = true;
            }
        }
        validate();
    });

    // ── VALIDATE FORM ─────────────────────────────────────────
    var createBtn       = document.getElementById('createOrderBtn');
    var insufficientMsg = document.getElementById('insufficientBalanceMsg');

    function validate() {
        var balanceOk = isGuest || !selectedPrice || walletBalance >= selectedPrice;
        if (insufficientMsg) insufficientMsg.classList.toggle('hidden', !selectedPrice || balanceOk);

        var isMomo = isGuest && document.querySelector('input[name="guest_payment_method"]:checked')?.value === 'manual_momo';
        var senderOk = !isMomo || (document.getElementById('momoSenderName')?.value.trim().length > 0);

        var ok = !!(selectedNetwork && selectedSize && phoneValid && balanceOk && senderOk);
        createBtn.disabled = !ok;
        if (ok && selectedNetwork && networkColors[selectedNetwork]) {
            createBtn.style.background = networkColors[selectedNetwork].btn;
            createBtn.style.color = '#fff';
        } else {
            createBtn.style.background = '';
            createBtn.style.color = '';
        }
    }

    // ── GUEST PAYMENT TOGGLE ──────────────────────────────────
    if (isGuest) {
        window.toggleGuestPayment = function() {
            var method = document.querySelector('input[name="guest_payment_method"]:checked').value;
            document.getElementById('momoDetailsSection').classList.toggle('hidden', method !== 'manual_momo');
            var pActive   = 'flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all border-slate-900 bg-slate-900';
            var pInactive = 'flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all border-slate-200 bg-slate-50 hover:border-slate-300';
            document.getElementById('method-paystack-label').className = method === 'paystack' ? pActive : pInactive;
            document.getElementById('method-momo-label').className     = method === 'manual_momo' ? pActive : pInactive;
            validate();
        };
        var momoName = document.getElementById('momoSenderName');
        if (momoName) momoName.addEventListener('input', validate);
        toggleGuestPayment();
    }

    // ── MODAL ─────────────────────────────────────────────────
    var modal = document.getElementById('confirmModal');
    var panel = document.getElementById('confirmPanel');

    window.showConfirmModal = function() {
        document.getElementById('summaryNetwork').textContent = selectedNetwork || '—';
        document.getElementById('summaryPackage').textContent = selectedSize    || '—';
        document.getElementById('summaryPhone').textContent   = phoneInput.value || '—';
        document.getElementById('summaryPrice').textContent   = 'GH\u20B5' + selectedPrice.toFixed(2);
        document.getElementById('formNetwork').value  = selectedNetwork;
        document.getElementById('formPackage').value  = selectedSize;
        document.getElementById('formPhone').value    = phoneInput.value;

        var c = networkColors[selectedNetwork];
        if (c) {
            var headerBar  = document.getElementById('modalHeaderBar');
            var dot        = document.getElementById('modalNetworkDot');
            var amountRow  = document.getElementById('modalAmountRow');
            var confirmBtn = document.getElementById('checkoutSubmitBtn');
            var summaryNet = document.getElementById('summaryNetwork');

            if (headerBar)  { headerBar.style.background = c.gradient; headerBar.style.height = '4px'; }
            if (dot)        { dot.style.background = c.dot; }
            if (amountRow)  { amountRow.style.backgroundColor = c.dot + '18'; }
            if (confirmBtn) { confirmBtn.style.background = c.btn; }
            if (summaryNet) { summaryNet.style.color = c.dot; }
        }

        if (isGuest) {
            var pm = document.querySelector('input[name="guest_payment_method"]:checked').value;
            document.getElementById('formPaymentMethod').value = pm;
            document.getElementById('formSenderName').value = pm === 'manual_momo'
                ? document.getElementById('momoSenderName').value.trim() : '';
        } else {
            document.getElementById('formPaymentMethod').value = 'wallet';
            document.getElementById('formSenderName').value    = '';
        }

        modal.classList.remove('opacity-0','pointer-events-none');
        panel.classList.remove('translate-y-8','sm:scale-95');
        document.body.style.overflow = 'hidden';
    };

    window.closeConfirmModal = function() {
        modal.classList.add('opacity-0','pointer-events-none');
        panel.classList.add('translate-y-8','sm:scale-95');
        document.body.style.overflow = '';
    };

    // Submit loading state
    document.getElementById('checkoutForm').addEventListener('submit', function() {
        var btn = document.getElementById('checkoutSubmitBtn');
        btn.innerHTML = '<svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing…';
        btn.disabled = true;
    });

    // ── BULK ORDERS ───────────────────────────────────────────
    var bulkInput     = document.getElementById('bulkInput');
    var bulkParseBtn  = document.getElementById('bulkParseBtn');
    var bulkClearBtn  = document.getElementById('bulkClearBtn');
    var bulkErrors    = document.getElementById('bulkErrors');
    var rowsContainer = document.getElementById('rowsContainer');
    var emptyState    = document.getElementById('emptyState');
    var rowCountEl    = document.getElementById('rowCount');
    var bulkTotalEl   = document.getElementById('bulkTotal');
    var bulkSubmitBtn = document.getElementById('bulkSubmitBtn');
    var bulkIdx       = 0;

    function detectNetwork(phone) {
        var pre = phone.slice(0,3);
        for (var n in networkPrefixes)
            if (networkPrefixes[n].indexOf(pre) !== -1) return n;
        return null;
    }

    function findPackage(net, sz) {
        var norm = sz.trim().replace(/\s+/g,'').toUpperCase();
        return (pricingData[net]||[]).find(function(p){ return p.size.replace(/\s+/g,'').toUpperCase()===norm; }) || null;
    }

    function addBulkRow(network, pkgSize, phone, price) {
        if (emptyState) emptyState.style.display = 'none';
        var idx = bulkIdx++;
        var dot = network==='MTN' ? 'bg-amber-400' : network==='Telecel' ? 'bg-red-500' : 'bg-blue-500';
        var row = document.createElement('div');
        row.className = 'bulk-row flex items-center justify-between px-4 py-3 group hover:bg-slate-50 transition-colors';
        row.innerHTML =
            '<div class="flex items-center gap-3 min-w-0">' +
                '<input type="hidden" name="orders['+idx+'][network_type]" value="'+network+'">' +
                '<input type="hidden" name="orders['+idx+'][package_size]" value="'+pkgSize+'">' +
                '<input type="hidden" name="orders['+idx+'][phone_number]" value="'+phone+'">' +
                '<span class="w-2 h-2 rounded-full shrink-0 '+dot+'"></span>' +
                '<span class="text-xs font-bold text-slate-600 shrink-0">'+pkgSize+'</span>' +
                '<span class="text-xs font-mono text-slate-400 truncate">'+phone+'</span>' +
            '</div>' +
            '<div class="flex items-center gap-3 shrink-0">' +
                '<span class="price-cell text-sm font-bold text-slate-900">GH\u20B5'+parseFloat(price).toFixed(2)+'</span>' +
                '<button type="button" class="remove-row w-6 h-6 rounded-full flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition opacity-0 group-hover:opacity-100">' +
                    '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>' +
            '</div>';
        rowsContainer.appendChild(row);
        row.querySelector('.remove-row').addEventListener('click', function(){ row.remove(); refreshBulk(); });
        refreshBulk();
    }

    function refreshBulk() {
        var rows = rowsContainer.querySelectorAll('.bulk-row');
        var total = Array.from(rows).reduce(function(s, r) {
            var c = r.querySelector('.price-cell');
            return s + (c ? parseFloat(c.textContent.replace('GH\u20B5',''))||0 : 0);
        }, 0);
        bulkTotalEl.textContent  = 'GH\u20B5'+total.toFixed(2);
        rowCountEl.textContent   = rows.length + (rows.length===1?' order':' orders');
        bulkSubmitBtn.disabled   = rows.length===0 || (!isGuest && total>walletBalance);
        if (rows.length===0 && emptyState) emptyState.style.display='';
    }

    function showBulkErrors(errs) {
        if (!errs.length) { bulkErrors.innerHTML=''; bulkErrors.classList.add('hidden'); return; }
        bulkErrors.innerHTML = errs.map(function(e){
            return '<div class="flex items-center gap-2 px-3 py-2 bg-red-50 border border-red-100 rounded-lg text-xs text-red-600">' +
                '<svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>'+e+'</div>';
        }).join('');
        bulkErrors.classList.remove('hidden');
    }

    if (bulkParseBtn) {
        bulkParseBtn.addEventListener('click', function() {
            var lines = (bulkInput.value||'').split('\n').map(function(l){return l.trim();}).filter(Boolean);
            if (!lines.length) { showBulkErrors(['Paste at least one order first.']); return; }
            var errs=[]; var added=0;
            lines.forEach(function(line, i) {
                var p = line.split(',').map(function(x){return x.trim();});
                if (p.length<2||!p[0]||!p[1]) { errs.push('Row '+(i+1)+': invalid format'); return; }
                var ph = p[0].replace(/\D/g,'').slice(0,10);
                if (ph.length!==10||!/^0[235]/.test(ph)) { errs.push('Row '+(i+1)+': invalid phone "'+p[0]+'"'); return; }
                var net = detectNetwork(ph);
                if (!net) { errs.push('Row '+(i+1)+': unknown network for '+ph); return; }
                var pkg = findPackage(net, p[1]);
                if (!pkg) { errs.push('Row '+(i+1)+': package "'+p[1]+'" not found for '+net); return; }
                addBulkRow(net, pkg.size, ph, pkg.price);
                added++;
            });
            showBulkErrors(errs);
            if (added) bulkInput.value='';
        });
    }

    if (bulkClearBtn) {
        bulkClearBtn.addEventListener('click', function() {
            rowsContainer.querySelectorAll('.bulk-row').forEach(function(r){r.remove();});
            showBulkErrors([]);
            if (emptyState) emptyState.style.display='';
            refreshBulk();
        });
    }

    document.querySelectorAll('.pkg-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            var sep = bulkInput.value&&!bulkInput.value.endsWith('\n')?'\n':'';
            bulkInput.value += sep + this.dataset.pkg + ',';
            bulkInput.focus();
        });
    });

    if (document.getElementById('bulkForm')) {
        document.getElementById('bulkForm').addEventListener('submit', function() {
            bulkSubmitBtn.innerHTML = '<svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Placing…';
            bulkSubmitBtn.disabled = true;
        });
    }
})();
</script>
@endpush

@endsection
