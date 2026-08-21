@extends('layouts.user')
@section('title', 'Buy Data')
@section('page-title', 'Buy Data Bundle')
@section('page-description', 'Purchase data bundles for any network')
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

    $netStyles = [
        'MTN'       => ['active' => 'bg-gradient-to-br from-amber-100 to-amber-50 border-amber-300 text-amber-700',  'ring' => 'ring-amber-400'],
        'Telecel'   => ['active' => 'bg-gradient-to-br from-red-100 to-red-50 border-red-300 text-red-700',          'ring' => 'ring-red-400'],
        'AirtelTigo'=> ['active' => 'bg-gradient-to-br from-blue-100 to-blue-50 border-blue-300 text-blue-700',       'ring' => 'ring-blue-400'],
    ];
@endphp

<div class="max-w-2xl mx-auto space-y-6">

    {{-- ===== MODE TABS (agents only) ===== --}}
    @unless($isGuest ?? false)
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-1.5 flex gap-1.5" id="modeTabs">
        <button type="button"
            class="mode-tab flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 bg-[#2563EB] text-white shadow-md"
            data-mode="single">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Single Order
        </button>
        <button type="button"
            class="mode-tab flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 bg-slate-100 text-slate-500 hover:bg-slate-200"
            data-mode="bulk">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Bulk Orders
        </button>
    </div>
    @endunless

    {{-- ===== SINGLE ORDER PANEL ===== --}}
    <div id="singleMode" class="bg-white border border-slate-100 shadow-xl shadow-slate-200/40 rounded-3xl p-6 sm:p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-gradient-to-br from-blue-50 to-transparent blur-3xl pointer-events-none"></div>

        <form id="singleOrderForm" onsubmit="event.preventDefault(); showConfirmModal();" autocomplete="off">

            {{-- 1. Network --}}
            <div class="space-y-3 relative z-10">
                <label class="block text-sm font-bold text-slate-800">1. Select Network</label>
                <div class="grid grid-cols-3 gap-3" id="networkCards">
                    @foreach($allNetworks as $network)
                        @php
                            $hasPackages = in_array($network, $availableNetworks);
                            $isFirst     = $loop->first && $hasPackages;
                            $activeClass = $netStyles[$network]['active'] . ' ' . $netStyles[$network]['ring'] . ' ring-2 ring-offset-2 border-2';
                            $inactiveClass = 'bg-white border-2 border-slate-200 text-slate-500 hover:border-slate-300 hover:bg-slate-50';
                            $disabledClass = 'bg-slate-50 border-2 border-slate-200 text-slate-300 opacity-50 cursor-not-allowed';
                        @endphp
                        <button
                            type="button"
                            class="network-card relative flex flex-col items-center justify-center gap-1 py-4 px-2 rounded-2xl transition-all duration-200 {{ $isFirst ? $activeClass : ($hasPackages ? $inactiveClass : $disabledClass) }}"
                            data-network="{{ $network }}"
                            data-available="{{ $hasPackages ? 'true' : 'false' }}"
                            @if(!$hasPackages) disabled @endif>

                            <span class="text-sm font-black tracking-wide leading-tight">{{ $network }}</span>

                            @if(!$hasPackages)
                                <span class="text-[9px] font-semibold opacity-60 uppercase tracking-wider">N/A</span>
                            @endif

                            {{-- active check badge --}}
                            @if($isFirst)
                            <div class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white flex items-center justify-center active-check">
                                <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
                            </div>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- 2. Package --}}
            <div class="space-y-3 mt-7 relative z-10">
                <label class="block text-sm font-bold text-slate-800">2. Select Package</label>
                <div class="relative">
                    <select id="packageSelect"
                        class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-200 rounded-2xl text-base text-slate-800 focus:border-[#2563EB] focus:bg-white outline-none transition font-medium appearance-none shadow-sm"
                        required>
                        <option value="">Choose a package…</option>
                        @foreach($pricing as $network => $packages)
                            @foreach($packages as $pkg)
                                <option
                                    value="{{ $pkg->package_size }}"
                                    data-network="{{ $network }}"
                                    data-price="{{ $pkg->selling_price }}">
                                    {{ $pkg->package_size }} — GH&#8373;{{ number_format($pkg->selling_price, 2) }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                    </div>
                </div>
            </div>

            {{-- 3. Recipient --}}
            <div class="space-y-2 mt-7 relative z-10">
                <div class="flex justify-between items-center">
                    <label class="text-sm font-bold text-slate-800">3. Recipient Number</label>
                    <span id="phoneStatus" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">0 / 10</span>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <input
                        type="tel"
                        id="phoneNumber"
                        inputmode="numeric"
                        placeholder="e.g. 0241234567"
                        maxlength="10"
                        autocomplete="off"
                        class="w-full pl-11 pr-12 py-4 bg-slate-50 border-2 border-slate-200 rounded-2xl text-base text-slate-800 focus:border-[#2563EB] focus:bg-white outline-none transition font-medium tracking-wider shadow-sm"
                        required>
                    <div id="phoneCheckIcon" class="absolute inset-y-0 right-0 pr-4 flex items-center hidden">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p id="phoneError" class="hidden text-xs text-red-500 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="phoneErrorText">Invalid number</span>
                </p>
            </div>

            {{-- 4. Payment --}}
            <div class="space-y-3 mt-7 relative z-10">
                <label class="text-sm font-bold text-slate-800">4. Payment Method</label>
                @if($isGuest ?? false)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <label class="relative flex items-center justify-between p-4 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-[#2563EB] hover:bg-blue-50 transition-all" id="method-paystack-label">
                            <input type="radio" name="guest_payment_method" value="paystack" class="peer sr-only" checked onchange="toggleGuestPayment()">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Paystack</p>
                                    <p class="text-[10px] text-slate-500">Card / Bank</p>
                                </div>
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center peer-checked:border-[#2563EB] peer-checked:bg-[#2563EB]">
                                <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </label>
                        
                        <label class="relative flex items-center justify-between p-4 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-[#2563EB] hover:bg-blue-50 transition-all" id="method-momo-label">
                            <input type="radio" name="guest_payment_method" value="manual_momo" class="peer sr-only" onchange="toggleGuestPayment()">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">MoMo Pay</p>
                                    <p class="text-[10px] text-slate-500">Manual Transfer</p>
                                </div>
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center peer-checked:border-[#2563EB] peer-checked:bg-[#2563EB]">
                                <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </label>
                    </div>

                    <div id="momoDetailsSection" class="hidden space-y-4 p-4 rounded-2xl bg-amber-50 border border-amber-200">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 bg-white rounded-xl border border-amber-100 shadow-sm">
                            <div>
                                <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Send Payment To</p>
                                <p class="text-sm font-black text-slate-800">{{ $momoName ?? 'Admin' }}</p>
                                <p class="text-lg font-mono font-bold text-slate-800">{{ $momoNumber ?? 'Not Configured' }}</p>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Name on MoMo Account</label>
                            <input type="text" id="momoSenderName" placeholder="e.g. John Doe" class="w-full px-4 py-3 bg-white border border-amber-200 rounded-xl text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 outline-none transition">
                            <p class="text-[10px] text-amber-700/70 mt-1">Provide the exact name you used for the transfer so we can verify your payment.</p>
                        </div>
                    </div>

                @else
                    <div class="flex items-center justify-between p-4 rounded-2xl border-2 border-[#2563EB] bg-blue-50 ring-2 ring-[#2563EB] ring-offset-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">My Wallet</p>
                                <p class="text-xs text-slate-500">Balance: <span class="font-bold text-slate-700">GH&#8373;{{ number_format($agent->balance ?? 0, 2) }}</span></p>
                            </div>
                        </div>
                        <svg class="w-6 h-6 text-[#2563EB]" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/></svg>
                    </div>
                    <div id="insufficientBalanceMsg" class="hidden mt-1 p-3 bg-red-50 border border-red-100 rounded-xl flex items-start gap-2">
                        <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <div>
                            <p class="text-xs text-red-700 font-medium">Insufficient balance for this package.</p>
                            <a href="{{ route('user.wallet.topup') }}" class="text-[11px] font-bold text-red-600 hover:underline">Top up wallet &rarr;</a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 5. Create Order --}}
            <div class="mt-8 pt-5 border-t border-slate-100 relative z-10">
                <button type="submit" id="createOrderBtn" disabled
                    class="w-full py-4 bg-[#2563EB] hover:bg-blue-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black text-base rounded-2xl shadow-xl shadow-blue-500/25 transition-all duration-200 flex items-center justify-center gap-2 active:scale-[0.98]">
                    Create Order
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </form>
    </div>

    {{-- ===== BULK ORDER PANEL ===== --}}
    <div id="bulkMode" class="hidden space-y-4">
        <form method="POST" action="{{ route('user.bulk-orders.store') }}" id="bulkForm">
            @csrf

            {{-- Input area --}}
            <div class="bg-white border border-slate-100 shadow-xl shadow-slate-200/40 rounded-3xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Paste Bulk Orders</h3>
                        <p class="text-xs text-slate-500 mt-0.5">One per line &mdash; format: <code class="bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded font-mono text-[11px]">phone,package</code></p>
                    </div>
                    <svg class="w-5 h-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>

                {{-- Available Packages Reference --}}
                <div class="px-5 pt-4 pb-2">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Available Packages</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($pricing as $network => $packages)
                            @foreach($packages as $pkg)
                                <button type="button"
                                    class="pkg-chip px-2.5 py-1 rounded-lg text-[11px] font-bold border transition-all duration-150 cursor-pointer
                                        {{ $network === 'MTN' ? 'bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-100' : ($network === 'Telecel' ? 'bg-red-50 border-red-200 text-red-700 hover:bg-red-100' : 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100') }}"
                                    data-pkg="{{ $pkg->package_size }}"
                                    title="Click to copy: {{ $pkg->package_size }} &mdash; GH&#8373;{{ number_format($pkg->selling_price, 2) }}">
                                    {{ $pkg->package_size }}
                                    <span class="opacity-60 font-normal">GH&#8373;{{ number_format($pkg->selling_price, 2) }}</span>
                                </button>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="p-5 pt-3 space-y-3">
                    @php
                        $firstPkg = $pricing->first()?->first()?->package_size ?? '1GB';
                    @endphp
                    <textarea id="bulkInput" rows="5"
                        placeholder="0241234567,{{ $firstPkg }}&#10;0551234567,{{ $firstPkg }}&#10;0271234567,{{ $firstPkg }}"
                        class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-2xl text-sm text-slate-800 focus:border-[#2563EB] focus:bg-white outline-none transition font-mono leading-relaxed resize-y"></textarea>

                    <div id="bulkErrors" class="hidden space-y-1.5"></div>

                    <button type="button" id="bulkParseBtn"
                        class="w-full py-3.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm rounded-2xl transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Parse &amp; Preview Orders
                    </button>
                </div>
            </div>

            {{-- Preview table --}}
            <div class="bg-white border border-slate-100 shadow-xl shadow-slate-200/40 rounded-3xl overflow-hidden flex flex-col" style="max-height:55vh;">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between shrink-0">
                    <h3 class="text-sm font-bold text-slate-800">Preview</h3>
                    <span id="rowCount" class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-black rounded-full">0 orders</span>
                </div>

                <div id="rowsContainer" class="flex-1 overflow-y-auto divide-y divide-slate-100">
                    <div id="emptyState" class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <svg class="w-12 h-12 mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p class="text-sm font-medium">Paste orders above then click Preview.</p>
                    </div>
                </div>

                <div class="p-5 bg-slate-50 border-t border-slate-100 shrink-0">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Estimated Total</span>
                        <span class="text-2xl font-black text-[#2563EB]" id="bulkTotal">GH&#8373;0.00</span>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" id="bulkSubmitBtn" disabled
                            class="flex-1 py-3.5 bg-[#2563EB] hover:bg-blue-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold text-sm rounded-2xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Place Orders Now
                        </button>
                        <button type="button" id="bulkClearBtn"
                            class="px-6 py-3.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-sm rounded-2xl transition-all">
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===== CONFIRMATION MODAL ===== --}}
<div id="confirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeConfirmModal()"></div>

    <div id="confirmPanel" class="relative w-full max-w-sm bg-white rounded-[2rem] shadow-2xl scale-95 transition-transform duration-300 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <h3 class="text-base font-black text-slate-800">Confirm Order</h3>
            <button type="button" onclick="closeConfirmModal()"
                class="w-8 h-8 rounded-full bg-slate-200 hover:bg-slate-300 flex items-center justify-center text-slate-500 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-5">
            <div class="bg-slate-50 rounded-2xl divide-y divide-slate-200 overflow-hidden">
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-sm text-slate-500 font-medium">Network</span>
                    <span id="summaryNetwork" class="text-sm font-bold text-slate-800 px-3 py-0.5 bg-white rounded-lg border border-slate-200">—</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-sm text-slate-500 font-medium">Package</span>
                    <span id="summaryPackage" class="text-sm font-bold text-slate-800">—</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-sm text-slate-500 font-medium">Recipient</span>
                    <span id="summaryPhone" class="text-base font-mono font-bold text-slate-800 tracking-widest">—</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3 bg-blue-50/60">
                    <span class="text-sm font-bold text-slate-700">Total</span>
                    <span id="summaryPrice" class="text-xl font-black text-[#2563EB]">GH&#8373;0.00</span>
                </div>
            </div>

            <form method="POST" action="{{ route('user.buy-data.store') }}" id="checkoutForm">
                @csrf
                <input type="hidden" name="network_type" id="formNetwork">
                <input type="hidden" name="package_size" id="formPackage">
                <input type="hidden" name="phone_number" id="formPhone">
                <input type="hidden" name="payment_method" id="formPaymentMethod" value="paystack">
                <input type="hidden" name="sender_name" id="formSenderName">

                <div class="flex flex-col gap-2 mt-4">
                    <button type="submit"
                        class="w-full py-4 bg-[#2563EB] hover:bg-blue-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-blue-500/25 transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Confirm &amp; Place Order
                    </button>
                    <button type="button" onclick="closeConfirmModal()"
                        class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-sm rounded-2xl transition-all">
                        Edit Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // ── DATA ──────────────────────────────────────────────────
    var pricingData   = @json($pricingJson);
    var walletBalance = {{ ($isGuest ?? false) ? 999999 : (float)($agent->balance ?? 0) }};
    var isGuest       = {{ ($isGuest ?? false) ? 'true' : 'false' }};

    var networkPrefixes = {
        MTN:       ['024','025','053','054','055','059'],
        Telecel:   ['020','050'],
        AirtelTigo:['027','057','026','056','023']
    };

    // Active card classes per network (mirror PHP $netStyles)
    var activeClassMap = {
        MTN:       'bg-gradient-to-br from-amber-100 to-amber-50 border-amber-300 text-amber-700 ring-amber-400 ring-2 ring-offset-2 border-2',
        Telecel:   'bg-gradient-to-br from-red-100 to-red-50 border-red-300 text-red-700 ring-red-400 ring-2 ring-offset-2 border-2',
        AirtelTigo:'bg-gradient-to-br from-blue-100 to-blue-50 border-blue-300 text-blue-700 ring-blue-400 ring-2 ring-offset-2 border-2'
    };
    var inactiveClass = 'bg-white border-2 border-slate-200 text-slate-500 hover:border-slate-300 hover:bg-slate-50';

    // ── MODE TABS ─────────────────────────────────────────────
    var modeTabs   = document.querySelectorAll('.mode-tab');
    var singleMode = document.getElementById('singleMode');
    var bulkMode   = document.getElementById('bulkMode');

    modeTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var mode = this.dataset.mode;
            modeTabs.forEach(function (t) {
                t.className = 'mode-tab flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 bg-slate-100 text-slate-500 hover:bg-slate-200';
            });
            this.className = 'mode-tab flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 bg-[#2563EB] text-white shadow-md';
            singleMode.classList.toggle('hidden', mode !== 'single');
            bulkMode.classList.toggle('hidden', mode !== 'bulk');
        });
    });

    // ── SINGLE ORDER ──────────────────────────────────────────
    var networkCards        = document.querySelectorAll('.network-card');
    var packageSelect       = document.getElementById('packageSelect');
    var phoneInput          = document.getElementById('phoneNumber');
    var phoneStatus         = document.getElementById('phoneStatus');
    var phoneCheckIcon      = document.getElementById('phoneCheckIcon');
    var phoneError          = document.getElementById('phoneError');
    var phoneErrorText      = document.getElementById('phoneErrorText');
    var createOrderBtn      = document.getElementById('createOrderBtn');
    var insufficientMsg     = document.getElementById('insufficientBalanceMsg');

    var selectedNetwork = null;
    var selectedPrice   = 0;

    // Auto-select the first available network on load
    var firstAvail = document.querySelector('.network-card[data-available="true"]');
    if (firstAvail) {
        selectedNetwork = firstAvail.dataset.network;
        filterPackages(selectedNetwork);
    }

    // Network card click
    networkCards.forEach(function (card) {
        card.addEventListener('click', function () {
            if (this.dataset.available !== 'true') return;
            selectNetwork(this.dataset.network);
        });
    });

    function selectNetwork(network) {
        // Reset all cards
        networkCards.forEach(function (c) {
            if (c.dataset.available !== 'true') return;
            c.className = 'network-card relative flex flex-col items-center justify-center gap-1 py-4 px-2 rounded-2xl transition-all duration-200 ' + inactiveClass;
            var badge = c.querySelector('.active-check');
            if (badge) badge.remove();
        });

        // Activate selected
        var card = document.querySelector('.network-card[data-network="' + network + '"]');
        if (card) {
            card.className = 'network-card relative flex flex-col items-center justify-center gap-1 py-4 px-2 rounded-2xl transition-all duration-200 ' + activeClassMap[network];
            card.insertAdjacentHTML('beforeend',
                '<div class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white flex items-center justify-center active-check">' +
                '<svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>' +
                '</div>');
        }

        selectedNetwork = network;
        filterPackages(network);
    }

    function filterPackages(network) {
        var opts = packageSelect.options;
        for (var i = 0; i < opts.length; i++) {
            if (!opts[i].value) continue;
            opts[i].style.display = (opts[i].dataset.network === network) ? '' : 'none';
        }
        packageSelect.value = '';
        selectedPrice = 0;
        validateForm();
    }

    packageSelect.addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        selectedPrice = (opt && opt.value) ? parseFloat(opt.dataset.price) || 0 : 0;
        validateForm();
    });

    phoneInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
        validateForm();
    });

    function validateForm() {
        var phone = phoneInput.value;
        var len   = phone.length;

        phoneStatus.textContent = len + ' / 10';
        phoneStatus.className   = 'text-[10px] font-bold uppercase tracking-wider ' + (len === 10 ? 'text-emerald-500' : 'text-slate-400');

        var phoneValid = false;
        phoneCheckIcon.classList.add('hidden');
        phoneError.classList.add('hidden');

        if (len === 10) {
            var prefix = phone.slice(0, 3);
            if (!/^0[235]/.test(phone)) {
                phoneErrorText.textContent = 'Invalid number prefix';
                phoneError.classList.remove('hidden');
            } else if (selectedNetwork && networkPrefixes[selectedNetwork] && networkPrefixes[selectedNetwork].indexOf(prefix) === -1) {
                phoneErrorText.textContent = "Number doesn't match " + selectedNetwork;
                phoneError.classList.remove('hidden');
            } else {
                phoneCheckIcon.classList.remove('hidden');
                phoneValid = true;
            }
        }

        var balanceOk = isGuest || selectedPrice === 0 || walletBalance >= selectedPrice;
        if (insufficientMsg) {
            insufficientMsg.classList.toggle('hidden', selectedPrice === 0 || balanceOk);
        }

        var isMomoSelected = isGuest && document.querySelector('input[name="guest_payment_method"]:checked')?.value === 'manual_momo';
        var senderNameValid = !isMomoSelected || (document.getElementById('momoSenderName')?.value.trim().length > 0);

        createOrderBtn.disabled = !(selectedNetwork && packageSelect.value && phoneValid && balanceOk && senderNameValid);
    }

    if (isGuest) {
        window.toggleGuestPayment = function() {
            var method = document.querySelector('input[name="guest_payment_method"]:checked').value;
            document.getElementById('momoDetailsSection').classList.toggle('hidden', method !== 'manual_momo');
            
            // Toggle active styling
            document.getElementById('method-paystack-label').className = method === 'paystack' 
                ? 'relative flex items-center justify-between p-4 rounded-2xl border-2 border-[#2563EB] bg-blue-50 cursor-pointer transition-all'
                : 'relative flex items-center justify-between p-4 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-[#2563EB] hover:bg-blue-50 transition-all';
                
            document.getElementById('method-momo-label').className = method === 'manual_momo'
                ? 'relative flex items-center justify-between p-4 rounded-2xl border-2 border-[#2563EB] bg-blue-50 cursor-pointer transition-all'
                : 'relative flex items-center justify-between p-4 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-[#2563EB] hover:bg-blue-50 transition-all';
                
            validateForm();
        };

        var momoSenderName = document.getElementById('momoSenderName');
        if (momoSenderName) {
            momoSenderName.addEventListener('input', validateForm);
        }
        toggleGuestPayment(); // init styling
    }

    // ── MODAL ─────────────────────────────────────────────────
    var confirmModal = document.getElementById('confirmModal');
    var confirmPanel = document.getElementById('confirmPanel');

    window.showConfirmModal = function () {
        document.getElementById('summaryNetwork').textContent = selectedNetwork || '—';
        document.getElementById('summaryPackage').textContent = packageSelect.value || '—';
        document.getElementById('summaryPhone').textContent   = phoneInput.value || '—';
        document.getElementById('summaryPrice').textContent   = 'GH\u20B5' + selectedPrice.toFixed(2);
        document.getElementById('formNetwork').value = selectedNetwork;
        document.getElementById('formPackage').value = packageSelect.value;
        document.getElementById('formPhone').value   = phoneInput.value;
        
        if (isGuest) {
            var pm = document.querySelector('input[name="guest_payment_method"]:checked').value;
            document.getElementById('formPaymentMethod').value = pm;
            if (pm === 'manual_momo') {
                document.getElementById('formSenderName').value = document.getElementById('momoSenderName').value.trim();
            } else {
                document.getElementById('formSenderName').value = '';
            }
        } else {
            document.getElementById('formPaymentMethod').value = 'wallet';
            document.getElementById('formSenderName').value = '';
        }

        confirmModal.classList.remove('opacity-0', 'pointer-events-none');
        confirmPanel.classList.remove('scale-95');
        document.body.style.overflow = 'hidden';
    };

    window.closeConfirmModal = function () {
        confirmModal.classList.add('opacity-0', 'pointer-events-none');
        confirmPanel.classList.add('scale-95');
        document.body.style.overflow = '';
    };

    // ── BULK ORDER ────────────────────────────────────────────
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
        var prefix = phone.slice(0, 3);
        for (var net in networkPrefixes) {
            if (networkPrefixes[net].indexOf(prefix) !== -1) return net;
        }
        return null;
    }

    function findPackage(net, sizeStr) {
        var list = pricingData[net] || [];
        // Normalise: strip spaces, uppercase — so "2 GB" matches "2GB" etc.
        var norm = sizeStr.trim().replace(/\s+/g, '').toUpperCase();
        for (var i = 0; i < list.length; i++) {
            if (list[i].size.replace(/\s+/g, '').toUpperCase() === norm) return list[i];
        }
        return null;
    }

    function addBulkRow(network, pkgSize, phone, price) {
        if (emptyState) emptyState.style.display = 'none';
        var idx  = bulkIdx++;
        var netBadge = network === 'MTN' ? 'bg-amber-100 text-amber-700' :
                       network === 'Telecel' ? 'bg-red-100 text-red-700' :
                       'bg-blue-100 text-blue-700';

        var row = document.createElement('div');
        row.className = 'bulk-row px-4 py-3 flex items-center justify-between group';
        row.innerHTML =
            '<div class="flex items-center gap-3 min-w-0">' +
                '<input type="hidden" name="orders[' + idx + '][network_type]" value="' + network + '">' +
                '<input type="hidden" name="orders[' + idx + '][package_size]" value="' + pkgSize + '">' +
                '<input type="hidden" name="orders[' + idx + '][phone_number]" value="' + phone + '">' +
                '<span class="shrink-0 px-2 py-0.5 rounded text-[10px] font-bold ' + netBadge + '">' + network + '</span>' +
                '<span class="text-sm font-bold text-slate-700 shrink-0">' + pkgSize + '</span>' +
                '<span class="text-sm font-mono text-slate-500 truncate">' + phone + '</span>' +
            '</div>' +
            '<div class="flex items-center gap-2 shrink-0">' +
                '<span class="price-cell text-sm font-bold text-slate-800">GH\u20B5' + parseFloat(price).toFixed(2) + '</span>' +
                '<button type="button" title="Remove" class="remove-row w-7 h-7 rounded-full flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition opacity-0 group-hover:opacity-100">' +
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>' +
            '</div>';

        rowsContainer.appendChild(row);
        row.querySelector('.remove-row').addEventListener('click', function () {
            row.remove();
            refreshBulkSummary();
        });
        refreshBulkSummary();
    }

    function refreshBulkSummary() {
        var rows  = rowsContainer.querySelectorAll('.bulk-row');
        var total = 0;
        rows.forEach(function (r) {
            var cell = r.querySelector('.price-cell');
            if (cell) total += parseFloat(cell.textContent.replace('GH\u20B5', '')) || 0;
        });
        bulkTotalEl.textContent  = 'GH\u20B5' + total.toFixed(2);
        rowCountEl.textContent   = rows.length + ' order' + (rows.length !== 1 ? 's' : '');
        bulkSubmitBtn.disabled   = rows.length === 0 || (!isGuest && total > walletBalance);

        if (rows.length === 0 && emptyState) emptyState.style.display = '';
    }

    function showBulkErrors(errors) {
        if (!errors.length) { bulkErrors.innerHTML = ''; bulkErrors.classList.add('hidden'); return; }
        bulkErrors.innerHTML = errors.map(function (e) {
            return '<div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded-lg text-xs font-medium">' +
                '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
                '<span>' + e + '</span></div>';
        }).join('');
        bulkErrors.classList.remove('hidden');
    }

    bulkParseBtn.addEventListener('click', function () {
        var lines = (bulkInput.value || '').split('\n').map(function (l) { return l.trim(); }).filter(Boolean);
        if (!lines.length) { showBulkErrors(['Paste at least one order before previewing.']); return; }

        var errors = [], added = 0;

        lines.forEach(function (line, i) {
            var parts = line.split(',').map(function (p) { return p.trim(); });
            if (parts.length < 2 || !parts[0] || !parts[1]) {
                errors.push('Row ' + (i + 1) + ': invalid format — expected <phone>,<package>'); return;
            }
            var phone = parts[0].replace(/\D/g, '').slice(0, 10);
            if (phone.length !== 10 || !/^0[235]/.test(phone)) {
                errors.push('Row ' + (i + 1) + ': invalid phone "' + parts[0] + '"'); return;
            }
            var net = detectNetwork(phone);
            if (!net) {
                errors.push('Row ' + (i + 1) + ': unknown network for ' + phone); return;
            }
            var pkg = findPackage(net, parts[1]);
            if (!pkg) {
                var available = (pricingData[net] || []).map(function (p) { return p.size; }).join(', ');
                var hint = available ? ' Available: ' + available : '';
                errors.push('Row ' + (i + 1) + ': package "' + parts[1] + '" not found for ' + net + '.' + hint); return;
            }
            addBulkRow(net, pkg.size, phone, pkg.price);
            added++;
        });

        showBulkErrors(errors);
        if (added > 0) bulkInput.value = '';
    });

    bulkClearBtn.addEventListener('click', function () {
        rowsContainer.querySelectorAll('.bulk-row').forEach(function (r) { r.remove(); });
        bulkInput.value = '';
        showBulkErrors([]);
        refreshBulkSummary();
    });

    // Package chip click — appends a sample line to the textarea
    document.querySelectorAll('.pkg-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var pkg = this.dataset.pkg;
            var cur = bulkInput.value.trimEnd();
            bulkInput.value = (cur ? cur + '\n' : '') + '0241234567,' + pkg;
            bulkInput.focus();
            // Flash the chip to give feedback
            this.style.opacity = '0.4';
            var self = this;
            setTimeout(function () { self.style.opacity = ''; }, 250);
        });
    });

    // Override error message to include available package list for that network
    var _origFindPackage = findPackage;
    function findPackageWithHint(net, sizeStr) {
        var result = findPackage(net, sizeStr);
        return result;
    }

})();
</script>
@endpush
@endsection
