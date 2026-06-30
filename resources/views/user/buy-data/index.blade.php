@extends('layouts.user')
@section('title', 'Buy Data')
@section('page-title', 'Buy Data Bundle')
@section('page-description', 'Purchase data bundles for any network')
@section('content')

@php
    $availableNetworks = $pricing->keys()->toArray();
    $allNetworks = ['MTN', 'Telecel', 'AirtelTigo'];
    $cartCount = !$isGuest ? \App\Models\CartItem::where('agent_id', session('user_id'))->sum('quantity') : 0;
    $pricingJson = [];
    foreach ($pricing as $network => $packages) {
        $pricingJson[$network] = $packages->map(fn($p) => [
            'size' => $p->package_size,
            'price' => (float) $p->selling_price,
            'validity' => $p->data_validity ?? '30 Days',
        ])->values();
    }
@endphp

<div class="space-y-6">
    {{-- Mode Tabs: Single / Bulk --}}
    @unless($isGuest ?? false)
    <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl p-1.5 flex gap-1.5" id="modeTabs">
        <button type="button" class="mode-tab flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 bg-[#EA580C] text-white shadow-md shadow-orange-500/15" data-mode="single">
            <x-heroicon-o-shopping-cart class="w-4 h-4" /> Single Order
        </button>
        <button type="button" class="mode-tab flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 bg-slate-100 text-slate-500 hover:bg-slate-200" data-mode="bulk">
            <x-heroicon-o-queue-list class="w-4 h-4" /> Bulk Orders
        </button>
    </div>
    @endunless

    {{-- ==================== SINGLE ORDER ==================== --}}
    <div id="singleMode">
        {{-- Network Tabs --}}
        <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl p-2 flex gap-2" id="networkTabs">
            @foreach($allNetworks as $network)
                @php
                    $hasPackages = in_array($network, $availableNetworks);
                    $color = match($network) {
                        'MTN' => ['active' => 'bg-amber-500 text-white shadow-lg shadow-amber-500/25', 'inactive' => 'bg-amber-50 text-amber-700 hover:bg-amber-100', 'dot' => 'bg-amber-400'],
                        'Telecel' => ['active' => 'bg-red-500 text-white shadow-lg shadow-red-500/25', 'inactive' => 'bg-red-50 text-red-700 hover:bg-red-100', 'dot' => 'bg-red-400'],
                        'AirtelTigo' => ['active' => 'bg-blue-600 text-white shadow-lg shadow-blue-600/25', 'inactive' => 'bg-blue-50 text-blue-700 hover:bg-blue-100', 'dot' => 'bg-blue-500'],
                    };
                    $isActive = $loop->first && $hasPackages;
                @endphp
                <button
                    type="button"
                    class="network-tab flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all duration-200 {{ $isActive ? $color['active'] : ($hasPackages ? $color['inactive'] : 'bg-slate-100 text-slate-400 cursor-not-allowed opacity-50') }}"
                    data-network="{{ $network }}"
                    data-available="{{ $hasPackages ? 'true' : 'false' }}"
                    {{ !$hasPackages ? 'disabled' : '' }}
                >
                    <span class="w-2 h-2 rounded-full {{ $isActive ? $color['dot'] : '' }} transition-all"></span>
                    {{ $network }}
                    @unless($hasPackages)
                        <span class="text-[9px] font-normal opacity-60">N/A</span>
                    @endunless
                </button>
            @endforeach
        </div>

        {{-- Package Dropdown --}}
        <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800" id="gridTitle">{{ $availableNetworks[0] ?? 'Packages' }} Packages</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Select a package to purchase</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Live</span>
                </div>
            </div>
            <div class="p-5">
                <div class="relative">
                    <select id="packageSelect" class="w-full px-4 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] focus:ring-0 focus:bg-white outline-none transition font-medium appearance-none">
                        <option value="">Choose a package...</option>
                        @foreach($pricing as $network => $packages)
                            @foreach($packages as $pkg)
                                <option
                                    value="{{ $pkg->package_size }}"
                                    data-network="{{ $network }}"
                                    data-price="{{ $pkg->selling_price }}"
                                    data-cost="{{ $pkg->cost }}"
                                    data-validity="{{ $pkg->data_validity ?? '30 Days' }}"
                                    class="pkg-option {{ !$loop->parent->first ? 'hidden' : '' }}"
                                    data-hidden="{{ !$loop->parent->first ? 'true' : 'false' }}"
                                >
                                    {{ $pkg->package_size }} — GH₵{{ number_format($pkg->selling_price, 2) }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    <x-heroicon-o-chevron-down class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5 pointer-events-none" />
                </div>
                <div id="noPackagesMsg" class="hidden text-center py-8">
                    <x-heroicon-o-inbox class="w-10 h-10 text-slate-300 mx-auto mb-2" />
                    <p class="text-sm text-slate-400 font-medium">No packages available for this network</p>
                </div>
            </div>
        </div>

        {{-- Buy Button --}}
        <button
            type="button"
            id="buyBtn"
            class="w-full py-4 bg-[#EA580C] hover:bg-[#C2410C] disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/20 transition-all duration-200 flex items-center justify-center gap-2 active:scale-[0.98]"
            disabled
        >
            <x-heroicon-o-paper-airplane class="w-4 h-4" />
            <span>Buy Now</span>
        </button>
    </div>

    {{-- ==================== BULK ORDER ==================== --}}
    <div id="bulkMode" class="hidden">
        <form method="POST" action="{{ route('user.bulk-orders.store') }}" id="bulkForm">
            @csrf

            {{-- Paste Input --}}
            <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl overflow-hidden mb-4">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800">Bulk Order</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Enter one order per line — <code class="bg-slate-100 px-1 py-0.5 rounded text-[10px]">phone,package</code></p>
                </div>
                <div class="p-5 space-y-3">
                    <div>
                        <textarea id="bulkInput" rows="6" placeholder="0551518931,2gb&#10;0241234567,1gb&#10;0271234567,5gb"
                                  class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] focus:ring-0 outline-none transition font-mono text-xs leading-relaxed"></textarea>
                    </div>
                    <div id="bulkErrors" class="hidden space-y-1"></div>
                    <button type="button" id="bulkParseBtn"
                            class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" /> Parse &amp; Add
                    </button>
                </div>
            </div>

            {{-- Parsed Rows --}}
            <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-slate-800">Orders</span>
                        <span id="rowCount" class="px-2 py-0.5 bg-[#EA580C]/10 text-[#EA580C] text-[10px] font-bold rounded-full">0</span>
                    </div>
                </div>
                <div id="rowsContainer" class="divide-y divide-slate-100 max-h-[40vh] overflow-y-auto"></div>
                <div class="px-5 py-3 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Estimated Total</span>
                    <span class="text-lg font-black text-[#EA580C]" id="bulkTotal">GH&#8373;0.00</span>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3 mt-4">
                <button type="submit" id="bulkSubmitBtn"
                        class="flex-1 py-3.5 bg-[#EA580C] hover:bg-[#C2410C] text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/20 transition-all duration-200 flex items-center justify-center gap-2 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                    <x-heroicon-o-shopping-cart class="w-4 h-4" />
                    <span>Add All to Cart</span>
                </button>
                <button type="button" id="bulkClearBtn"
                        class="px-5 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm rounded-xl transition">
                    Clear
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Checkout Modal --}}
<div id="checkoutModal" class="fixed inset-0 z-50 pointer-events-none opacity-0 flex items-center justify-center p-4" style="visibility: hidden;">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="modalOverlay"></div>
    <div class="relative w-full max-w-md max-h-[85vh] bg-white rounded-2xl shadow-2xl transition-all duration-300 flex flex-col scale-95 opacity-0" id="modalPanel">
        <div id="modalAccent" class="h-1 rounded-t-2xl bg-amber-500 transition-colors duration-300"></div>
        <div class="px-6 pt-5 pb-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button type="button" id="modalClose" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition shrink-0">
                    <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                </button>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Checkout</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5" id="modalSubtitle">Complete your data purchase</p>
                </div>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto px-6 pb-6 space-y-5">
            <div class="rounded-2xl p-4 transition-colors duration-300" id="modalSummaryCard">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" id="modalNetworkIcon">
                            <x-heroicon-o-signal class="w-6 h-6 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400" id="modalNetworkLabel">MTN</p>
                            <p class="text-2xl font-black text-slate-800 leading-none mt-0.5" id="modalPackageSize">1GB</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Total</p>
                        <p class="text-2xl font-black text-[#EA580C]" id="modalPrice">GH&#8373;5.00</p>
                    </div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-400">Recipient Phone Number</label>
                    <span class="text-[10px] font-bold text-slate-300" id="phoneCounter">0 / 10</span>
                </div>
                <div class="relative">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                        <span class="text-sm font-bold text-slate-600">+233</span>
                        <div class="w-px h-5 bg-slate-300"></div>
                    </div>
                    <input type="tel" id="modalPhone" placeholder="024 123 4567" maxlength="10"
                           class="w-full pl-[4.5rem] pr-12 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] focus:ring-0 focus:bg-white outline-none transition font-medium tracking-wide">
                    <div id="modalPhoneStatus" class="absolute right-4 top-1/2 -translate-y-1/2 hidden">
                        <x-heroicon-o-check-circle class="text-emerald-500 w-5 h-5" />
                    </div>
                </div>
                <p id="modalPhoneError" class="text-[11px] text-red-500 mt-2 hidden flex items-center gap-1">
                    <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                    <span id="modalPhoneErrorText">Enter a valid 10-digit Ghana number starting with 0</span>
                </p>
            </div>
            @unless($isGuest ?? false)
            <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-xl px-4 py-3.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <x-heroicon-o-wallet class="text-emerald-500 w-4 h-4" />
                    </div>
                    <span class="text-xs font-bold text-slate-500">Wallet Balance</span>
                </div>
                <span class="text-sm font-black text-slate-700">GH&#8373;{{ number_format($agent->balance ?? 0, 2) }}</span>
            </div>
            @endunless
            <div id="insufficientWarning" class="hidden bg-red-50 border border-red-200/60 rounded-xl px-4 py-3.5">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-exclamation-circle class="text-red-500 w-4 h-4 shrink-0" />
                    <p class="text-[11px] font-medium text-red-600">Insufficient wallet balance. Please top up first.</p>
                </div>
                <a href="{{ route('user.wallet.topup') }}" class="mt-2.5 inline-flex items-center gap-1 text-[11px] font-bold text-[#EA580C] hover:underline">
                    Top Up Wallet <x-heroicon-o-arrow-right class="w-3 h-3" />
                </a>
            </div>
            <div class="grid grid-cols-3 gap-2 pt-2">
                <div class="text-center p-3 bg-slate-50 rounded-xl">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center mx-auto mb-1.5"><x-heroicon-o-bolt class="text-emerald-500 w-4 h-4" /></div>
                    <p class="text-[10px] font-bold text-slate-700">Instant</p><p class="text-[9px] text-slate-400">Delivery</p>
                </div>
                <div class="text-center p-3 bg-slate-50 rounded-xl">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mx-auto mb-1.5"><x-heroicon-o-shield-check class="text-blue-500 w-4 h-4" /></div>
                    <p class="text-[10px] font-bold text-slate-700">Secure</p><p class="text-[9px] text-slate-400">{{ ($isGuest ?? false) ? 'Paystack' : 'Wallet' }}</p>
                </div>
                <div class="text-center p-3 bg-slate-50 rounded-xl">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center mx-auto mb-1.5"><x-heroicon-o-phone class="text-purple-500 w-4 h-4" /></div>
                    <p class="text-[10px] font-bold text-slate-700">24/7</p><p class="text-[9px] text-slate-400">Support</p>
                </div>
            </div>
        </div>
        <div class="px-6 pb-6 pt-4 border-t border-slate-100 bg-white">
            <form method="POST" action="{{ ($isGuest ?? false) ? route('user.buy-data.store') : route('user.cart.store') }}" id="checkoutForm">
                @csrf
                <input type="hidden" name="network_type" id="formNetwork">
                <input type="hidden" name="package_size" id="formPackage">
                <input type="hidden" name="phone_number" id="formPhone">
                <button type="submit" id="checkoutBtn"
                        class="w-full py-4 bg-[#EA580C] hover:bg-[#C2410C] disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/20 transition-all duration-200 flex items-center justify-center gap-2 active:scale-[0.98]" disabled>
                    @if($isGuest ?? false)
                        <x-heroicon-o-credit-card class="w-4 h-4" /><span>Pay with Paystack</span>
                    @else
                        <x-heroicon-o-shopping-cart class="w-4 h-4" /><span>Add to Cart</span>
                    @endif
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    // ========== SHARED ==========
    var pricingData = @json($pricingJson);
    var balance = {{ ($isGuest ?? false) ? 999999 : ($agent->balance ?? 0) }};
    var isGuest = {{ ($isGuest ?? false) ? 'true' : 'false' }};

    var networkPrefixes = {
        'MTN': ['024','025','053','054','055','059'],
        'Telecel': ['020','050'],
        'AirtelTigo': ['027','057','026','056','023']
    };

    // ========== MODE TABS ==========
    var modeTabs = document.querySelectorAll('.mode-tab');
    var singleMode = document.getElementById('singleMode');
    var bulkMode = document.getElementById('bulkMode');

    modeTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var mode = this.getAttribute('data-mode');
            modeTabs.forEach(function(t) {
                t.className = 'mode-tab flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 bg-slate-100 text-slate-500 hover:bg-slate-200';
            });
            this.className = 'mode-tab flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 bg-[#EA580C] text-white shadow-md shadow-orange-500/15';
            singleMode.classList.toggle('hidden', mode !== 'single');
            bulkMode.classList.toggle('hidden', mode !== 'bulk');
        });
    });

    // ========== SINGLE ORDER ==========
    var networkTabs = document.querySelectorAll('.network-tab');
    var packageSelect = document.getElementById('packageSelect');
    var buyBtn = document.getElementById('buyBtn');
    var modal = document.getElementById('checkoutModal');
    var modalOverlay = document.getElementById('modalOverlay');
    var modalPanel = document.getElementById('modalPanel');
    var modalClose = document.getElementById('modalClose');
    var modalPhone = document.getElementById('modalPhone');
    var modalPhoneStatus = document.getElementById('modalPhoneStatus');
    var modalPhoneError = document.getElementById('modalPhoneError');
    var modalPhoneErrorText = document.getElementById('modalPhoneErrorText');
    var checkoutBtn = document.getElementById('checkoutBtn');
    var formNetwork = document.getElementById('formNetwork');
    var formPackage = document.getElementById('formPackage');
    var formPhone = document.getElementById('formPhone');
    var insufficientWarning = document.getElementById('insufficientWarning');
    var gridTitle = document.getElementById('gridTitle');
    var noPackagesMsg = document.getElementById('noPackagesMsg');
    var phoneCounter = document.getElementById('phoneCounter');
    var modalAccent = document.getElementById('modalAccent');
    var modalSummaryCard = document.getElementById('modalSummaryCard');
    var modalNetworkIcon = document.getElementById('modalNetworkIcon');
    var modalNetworkLabel = document.getElementById('modalNetworkLabel');
    var selectedPrice = 0;

    var colorMap = {
        'MTN': { active: 'bg-amber-500 text-white shadow-lg shadow-amber-500/25', inactive: 'bg-amber-50 text-amber-700 hover:bg-amber-100', dot: 'bg-amber-400', accent: 'bg-amber-500', iconBg: 'bg-amber-100', iconText: 'text-amber-600', cardBg: 'bg-amber-50/50 border-amber-200/50' },
        'Telecel': { active: 'bg-red-500 text-white shadow-lg shadow-red-500/25', inactive: 'bg-red-50 text-red-700 hover:bg-red-100', dot: 'bg-red-400', accent: 'bg-red-500', iconBg: 'bg-red-100', iconText: 'text-red-600', cardBg: 'bg-red-50/50 border-red-200/50' },
        'AirtelTigo': { active: 'bg-blue-600 text-white shadow-lg shadow-blue-600/25', inactive: 'bg-blue-50 text-blue-700 hover:bg-blue-100', dot: 'bg-blue-500', accent: 'bg-blue-600', iconBg: 'bg-blue-100', iconText: 'text-blue-600', cardBg: 'bg-blue-50/50 border-blue-200/50' }
    };

    function resetTabStyles() {
        networkTabs.forEach(function(t) {
            var net = t.getAttribute('data-network');
            var available = t.getAttribute('data-available') === 'true';
            if (!available) {
                t.className = 'network-tab flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all duration-200 bg-slate-100 text-slate-400 cursor-not-allowed opacity-50';
            } else {
                t.className = 'network-tab flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all duration-200 ' + colorMap[net].inactive;
            }
            var dot = t.querySelector('span');
            if (dot) { dot.className = 'w-2 h-2 rounded-full transition-all'; }
        });
    }

    function setActiveTab(network) {
        resetTabStyles();
        var tab = document.querySelector('.network-tab[data-network="' + network + '"]');
        if (tab) {
            tab.className = 'network-tab flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all duration-200 ' + colorMap[network].active;
            var dot = tab.querySelector('span');
            if (dot) { dot.className = 'w-2 h-2 rounded-full ' + colorMap[network].dot + ' transition-all'; }
        }
    }

    function filterPackages(network) {
        var options = packageSelect.options;
        var count = 0;
        for (var i = 0; i < options.length; i++) {
            var opt = options[i];
            if (opt.value === '') continue;
            if (opt.getAttribute('data-network') === network) {
                opt.style.display = ''; opt.removeAttribute('data-hidden'); count++;
            } else {
                opt.style.display = 'none'; opt.setAttribute('data-hidden', 'true');
            }
        }
        packageSelect.value = '';
        gridTitle.textContent = network + ' Packages';
        noPackagesMsg.classList.toggle('hidden', count > 0);
        packageSelect.parentElement.classList.toggle('hidden', count === 0);
        buyBtn.disabled = true; selectedPrice = 0;
    }

    function applyNetworkTheme(network) {
        var c = colorMap[network]; if (!c) return;
        modalAccent.className = 'h-1 rounded-t-2xl transition-colors duration-300 ' + c.accent;
        modalSummaryCard.className = 'rounded-2xl p-4 border transition-colors duration-300 ' + c.cardBg;
        modalNetworkIcon.className = 'w-12 h-12 rounded-xl flex items-center justify-center shrink-0 ' + c.iconBg;
        modalNetworkIcon.querySelector('svg').className.baseVal = 'w-6 h-6 ' + c.iconText;
        modalNetworkLabel.textContent = network;
    }

    networkTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            if (this.getAttribute('data-available') !== 'true') return;
            setActiveTab(this.getAttribute('data-network'));
            filterPackages(this.getAttribute('data-network'));
        });
    });

    packageSelect.addEventListener('change', function() {
        var sel = this.options[this.selectedIndex];
        if (!sel || sel.value === '') { buyBtn.disabled = true; selectedPrice = 0; return; }
        selectedPrice = parseFloat(sel.getAttribute('data-price')) || 0;
        buyBtn.disabled = selectedPrice <= 0;
    });

    buyBtn.addEventListener('click', function() {
        var sel = packageSelect.options[packageSelect.selectedIndex];
        if (!sel || sel.value === '') return;
        var network = sel.getAttribute('data-network');
        formNetwork.value = network; formPackage.value = sel.value;
        selectedPrice = parseFloat(sel.getAttribute('data-price'));
        applyNetworkTheme(network);
        document.getElementById('modalPackageSize').textContent = sel.value;
        document.getElementById('modalPrice').textContent = 'GH\u20B5' + selectedPrice.toFixed(2);
        modalPhone.value = ''; modalPhoneStatus.classList.add('hidden'); modalPhoneError.classList.add('hidden');
        modalPhoneErrorText.textContent = 'Enter a valid 10-digit phone number';
        phoneCounter.textContent = '0 / 10'; phoneCounter.className = 'text-[10px] font-bold text-slate-300';
        checkoutBtn.disabled = true;
        if (!isGuest) { insufficientWarning.classList.toggle('hidden', balance >= selectedPrice); checkoutBtn.disabled = balance < selectedPrice; }
        openModal();
    });

    function openModal() {
        modal.style.visibility = 'visible'; modal.style.pointerEvents = 'auto'; modal.style.opacity = '1';
        requestAnimationFrame(function() { requestAnimationFrame(function() {
            modalOverlay.style.opacity = '1'; modalPanel.style.transform = 'scale(1)'; modalPanel.style.opacity = '1';
        }); }); document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        modalOverlay.style.opacity = '0'; modalPanel.style.transform = 'scale(0.95)'; modalPanel.style.opacity = '0';
        setTimeout(function() { modal.style.visibility = 'hidden'; modal.style.pointerEvents = 'none'; modal.style.opacity = '0'; document.body.style.overflow = ''; }, 300);
    }
    modalClose.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', closeModal);

    modalPhone.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 10);
        var len = this.value.length;
        phoneCounter.textContent = len + ' / 10';
        phoneCounter.className = 'text-[10px] font-bold ' + (len === 10 ? 'text-emerald-500' : len > 0 ? 'text-amber-500' : 'text-slate-300');
        if (len === 0) { modalPhoneStatus.classList.add('hidden'); modalPhoneError.classList.add('hidden'); checkoutBtn.disabled = true; return; }

        var phone = this.value;
        var valid = len === 10 && /^0[235]/.test(phone);
        var networkMatch = true;

        if (valid && len === 10) {
            var selectedNetwork = formNetwork.value;
            var prefix = phone.substring(0, 3);
            var prefixes = networkPrefixes[selectedNetwork] || [];
            if (prefixes.indexOf(prefix) === -1) {
                networkMatch = false;
                valid = false;
            }
        }

        if (valid && networkMatch) {
            modalPhoneStatus.classList.remove('hidden');
            modalPhoneError.classList.add('hidden');
            formPhone.value = phone;
            checkoutBtn.disabled = !isGuest && balance < selectedPrice;
        } else {
            modalPhoneStatus.classList.add('hidden');
            if (len === 10 && !networkMatch) {
                modalPhoneErrorText.textContent = 'Number doesn\'t belong to ' + formNetwork.value;
                modalPhoneError.classList.remove('hidden');
            } else {
                modalPhoneErrorText.textContent = 'Enter a valid 10-digit phone number';
                modalPhoneError.classList.toggle('hidden', len !== 10);
            }
            checkoutBtn.disabled = true;
        }
    });

    document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && modal.style.visibility === 'visible') closeModal(); });

    var firstNet = document.querySelector('.network-tab[data-available="true"]');
    if (firstNet) { setActiveTab(firstNet.getAttribute('data-network')); filterPackages(firstNet.getAttribute('data-network')); }

    // ========== BULK ORDER ==========
    var bulkInput = document.getElementById('bulkInput');
    var bulkParseBtn = document.getElementById('bulkParseBtn');
    var bulkClearBtn = document.getElementById('bulkClearBtn');
    var bulkErrors = document.getElementById('bulkErrors');
    var rowsContainer = document.getElementById('rowsContainer');
    var rowCount = document.getElementById('rowCount');
    var bulkTotal = document.getElementById('bulkTotal');
    var bulkIdx = 0;

    function detectNetwork(phone) {
        var prefix = phone.substring(0, 3);
        for (var net in networkPrefixes) {
            if (networkPrefixes[net].indexOf(prefix) !== -1) return net;
        }
        return null;
    }

    function findPackage(net, sizeStr) {
        var pkgs = pricingData[net] || [];
        var normalised = sizeStr.toUpperCase().trim();
        for (var i = 0; i < pkgs.length; i++) {
            if (pkgs[i].size.toUpperCase() === normalised) return pkgs[i];
        }
        return null;
    }

    function createBulkRow(network, pkgSize, phone, price) {
        var idx = bulkIdx++;
        var row = document.createElement('div');
        row.className = 'px-4 py-3 flex items-center gap-2 bulk-row';

        var netBadge = network === 'MTN' ? 'bg-amber-100 text-amber-700' :
                       network === 'Telecel' ? 'bg-red-100 text-red-700' :
                       'bg-blue-100 text-blue-700';

        row.innerHTML =
            '<span class="text-[10px] font-bold text-slate-300 w-5 text-center shrink-0">' + (rowsContainer.children.length + 1) + '</span>' +
            '<input type="hidden" name="orders[' + idx + '][network_type]" value="' + network + '">' +
            '<input type="hidden" name="orders[' + idx + '][package_size]" value="' + pkgSize + '">' +
            '<input type="hidden" name="orders[' + idx + '][phone_number]" value="' + phone + '">' +
            '<div class="flex-1 flex items-center gap-2">' +
                '<span class="px-1.5 py-0.5 rounded text-[9px] font-bold ' + netBadge + '">' + network + '</span>' +
                '<span class="text-xs font-bold text-slate-800">' + pkgSize + '</span>' +
                '<span class="text-[10px] text-slate-400">·</span>' +
                '<span class="text-xs text-slate-600 font-mono">' + phone + '</span>' +
            '</div>' +
            '<span class="text-xs font-bold text-[#EA580C] shrink-0">GH\u20B5' + parseFloat(price).toFixed(2) + '</span>' +
            '<button type="button" class="br-remove px-1.5 py-1 text-red-400 hover:text-red-600 rounded transition shrink-0">' +
                '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
            '</button>';

        rowsContainer.appendChild(row);
        row.querySelector('.br-remove').addEventListener('click', function() { row.remove(); renumberBulkRows(); updateBulkTotal(); });
        updateBulkTotal();
    }

    function renumberBulkRows() {
        var rows = rowsContainer.querySelectorAll('.bulk-row');
        rowCount.textContent = rows.length;
        rows.forEach(function(r, i) { var s = r.querySelector('span'); if (s) s.textContent = i + 1; });
    }

    function updateBulkTotal() {
        var rows = rowsContainer.querySelectorAll('.bulk-row');
        var total = 0;
        rows.forEach(function(r) {
            var hiddenPrice = r.querySelector('span.text-\\[\\#EA580C\\]');
            if (hiddenPrice) total += parseFloat(hiddenPrice.textContent.replace('GH\u20B5','')) || 0;
        });
        bulkTotal.textContent = 'GH\u20B5' + total.toFixed(2);
        rowCount.textContent = rows.length;
    }

    function showBulkErrors(errors) {
        if (errors.length === 0) { bulkErrors.classList.add('hidden'); bulkErrors.innerHTML = ''; return; }
        bulkErrors.innerHTML = errors.map(function(e) {
            return '<div class="flex items-center gap-2 bg-red-50 border border-red-200/60 text-red-600 px-3 py-2 rounded-xl text-[11px]">' +
                '<svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
                '<span>' + e + '</span></div>';
        }).join('');
        bulkErrors.classList.remove('hidden');
    }

    bulkParseBtn.addEventListener('click', function() {
        var lines = bulkInput.value.split('\n').map(function(l) { return l.trim(); }).filter(function(l) { return l.length > 0; });
        if (!lines.length) { showBulkErrors(['Enter at least one order in phone,package format.']); return; }

        var errors = [];
        var added = 0;

        lines.forEach(function(line, i) {
            var parts = line.split(',').map(function(p) { return p.trim(); });
            if (parts.length !== 2 || !parts[0] || !parts[1]) {
                errors.push('Line ' + (i+1) + ': "' + line + '" — invalid format. Use phone,package');
                return;
            }

            var phone = parts[0].replace(/\D/g, '').substring(0, 10);
            if (phone.length !== 10) {
                errors.push('Line ' + (i+1) + ': "' + parts[0] + '" — phone must be 10 digits');
                return;
            }
            if (!/^0[235]/.test(phone)) {
                errors.push('Line ' + (i+1) + ': "' + phone + '" — invalid phone prefix');
                return;
            }

            var net = detectNetwork(phone);
            if (!net) {
                errors.push('Line ' + (i+1) + ': "' + phone + '" — cannot detect network');
                return;
            }

            var pkg = findPackage(net, parts[1]);
            if (!pkg) {
                errors.push('Line ' + (i+1) + ': "' + parts[1] + '" — package not available on ' + net);
                return;
            }

            createBulkRow(net, pkg.size, phone, pkg.price);
            added++;
        });

        showBulkErrors(errors);
        if (added > 0) bulkInput.value = '';
    });

    bulkClearBtn.addEventListener('click', function() {
        rowsContainer.innerHTML = '';
        bulkTotal.textContent = 'GH\u20B50.00';
        rowCount.textContent = '0';
        showBulkErrors([]);
    });
})();
</script>
@endpush
@endsection
