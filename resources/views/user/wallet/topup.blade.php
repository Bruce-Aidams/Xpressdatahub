@extends('layouts.user')
@section('title', 'Top Up Wallet')
@section('page-title', 'Top Up Wallet')
@section('page-description', 'Fund your wallet via Paystack')
@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Top Up Wallet</h1>
        <p class="text-sm text-slate-400 mt-1">Fund your wallet via Paystack or MTN MoMo</p>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-sm mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Current Balance</p>
                <p class="text-3xl font-black text-[#EA580C] mt-1">GH&#8373;{{ number_format($agent->balance ?? 0, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#EA580C]/10 flex items-center justify-center">
                <x-heroicon-o-banknotes class="w-6 h-6 text-[#EA580C]" />
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="flex border-b border-slate-100">
            <button type="button" id="tabPaystack" onclick="switchTab('paystack')" class="flex-1 py-4 text-sm font-bold text-[#EA580C] border-b-2 border-[#EA580C] bg-orange-50/50 transition">Paystack</button>
            <button type="button" id="tabMomo" onclick="switchTab('momo')" class="flex-1 py-4 text-sm font-bold text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition">MTN MoMo</button>
        </div>
        
        {{-- Paystack Tab --}}
        <div id="contentPaystack" class="block">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Top Up via Paystack</h3>
            </div>
        <div class="p-6">
            <form method="POST" action="{{ route('user.wallet.topup.init') }}" id="topupForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Enter Amount</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">&#8373;</span>
                            <input
                                type="number"
                                name="amount"
                                id="amountInput"
                                min="{{ $minimumAmount }}"
                                max="100000"
                                step="0.01"
                                value="{{ old('amount', $minimumAmount) }}"
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-lg font-bold text-slate-800 focus:border-[#EA580C] focus:ring-2 focus:ring-[#EA580C]/10 outline-none transition"
                                required
                            >
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Minimum topup: GH&#8373;{{ number_format($minimumAmount, 2) }}</p>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                        <p class="text-xs font-bold text-slate-600 mb-3">Fee Breakdown</p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Amount</span>
                                <span class="font-bold text-slate-700" id="amountDisplay">GH&#8373;{{ number_format($minimumAmount, 2) }}</span>
                            </div>
                            @if($chargeAmount > 0)
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">
                                    Processing fee
                                    @if($chargeType === 'percentage')
                                        ({{ $chargeAmount }}%)
                                    @else
                                        (Fixed)
                                    @endif
                                </span>
                                <span class="font-bold text-slate-700" id="feeDisplay">GH&#8373;{{ number_format($chargeType === 'percentage' ? ($minimumAmount * $chargeAmount / 100) : $chargeAmount, 2) }}</span>
                            </div>
                            @endif
                            <div class="border-t border-slate-200 pt-2 flex justify-between text-sm">
                                <span class="font-bold text-slate-700">Total Payable</span>
                                <span class="font-black text-[#EA580C]" id="totalPayable">GH&#8373;{{ number_format($chargeType === 'percentage' ? ($minimumAmount + $minimumAmount * $chargeAmount / 100) : $minimumAmount + $chargeAmount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full mt-6 bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-bold rounded-xl py-3 transition shadow-md shadow-orange-500/10 flex items-center justify-center gap-2" id="submitBtn">
                    <x-heroicon-o-lock-closed class="w-5 h-5" />
                    <span>Pay with Paystack</span>
                </button>
            </form>

            <div class="mt-4 flex items-center justify-center gap-2 text-[10px] text-slate-400">
                <x-heroicon-o-shield-check class="text-emerald-500 w-4 h-4" />
                <span>Secured by Paystack. Your card details are never stored.</span>
            </div>
        </div>
        </div>

        {{-- MoMo Tab --}}
        <div id="contentMomo" class="hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-800">Manual MTN MoMo Top-up</h3>
            </div>
            <div class="p-6">
                
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
                    <p class="text-xs font-bold text-blue-800 mb-2">Instructions</p>
                    <ol class="text-[11px] text-blue-700 space-y-1.5 list-decimal list-inside">
                        <li>Send your top-up amount to the MTN MoMo number below.</li>
                        <li>Enter the exact amount sent and the name on your MoMo account below.</li>
                        <li>Click "I Have Paid" and wait for admin approval.</li>
                    </ol>
                </div>

                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 mb-6 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Send funds to</p>
                    <p class="text-2xl font-black text-slate-800 tracking-tight">{{ $momoNumber }}</p>
                    <p class="text-xs text-slate-500 mt-1 font-medium">{{ $momoName }}</p>
                </div>

                <form method="POST" action="{{ route('user.wallet.topup.manual') }}" id="momoForm">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Amount Sent (GH&#8373;) <span class="text-red-400">*</span></label>
                            <input type="number" name="amount" min="{{ $minimumAmount }}" max="100000" step="0.01" value="{{ old('amount', $minimumAmount) }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Sender Name (Your MoMo Name) <span class="text-red-400">*</span></label>
                            <input type="text" name="sender_name" placeholder="e.g. John Doe" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition" required>
                        </div>
                    </div>

                    <button type="submit" class="w-full mt-6 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl py-3 transition shadow-md shadow-blue-500/10 flex items-center justify-center gap-2">
                        <x-heroicon-o-check-circle class="w-5 h-5" />
                        <span>I Have Paid</span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    var successAlert = document.querySelector('[data-success-alert]');
    if (successAlert) {
        setTimeout(function() {
            window.location.href = '{{ route("user.wallet.topup") }}';
        }, 3000);
    }

    var amountInput = document.getElementById('amountInput');
    var amountDisplay = document.getElementById('amountDisplay');
    var feeDisplay = document.getElementById('feeDisplay');
    var totalDisplay = document.getElementById('totalDisplay');
    var totalPayable = document.getElementById('totalPayable');
    var chargeAmount = {{ $chargeAmount }};
    var chargeType = '{{ $chargeType }}';

    function updateTotals() {
        var amount = parseFloat(amountInput.value) || 0;
        var fee = 0;
        if (chargeType === 'percentage') {
            fee = (amount * chargeAmount) / 100;
        } else {
            fee = chargeAmount;
        }
        var total = amount + fee;

        amountDisplay.textContent = 'GH\u20B5' + amount.toFixed(2);
        if (feeDisplay) feeDisplay.textContent = 'GH\u20B5' + fee.toFixed(2);
        if (totalDisplay) totalDisplay.textContent = total.toFixed(2);
        totalPayable.textContent = 'GH\u20B5' + total.toFixed(2);
    }

    amountInput.addEventListener('input', updateTotals);
    updateTotals();

    function switchTab(tab) {
        var tabPaystack = document.getElementById('tabPaystack');
        var tabMomo = document.getElementById('tabMomo');
        var contentPaystack = document.getElementById('contentPaystack');
        var contentMomo = document.getElementById('contentMomo');

        if (tab === 'paystack') {
            tabPaystack.className = 'flex-1 py-4 text-sm font-bold text-[#EA580C] border-b-2 border-[#EA580C] bg-orange-50/50 transition';
            tabMomo.className = 'flex-1 py-4 text-sm font-bold text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition';
            contentPaystack.className = 'block';
            contentMomo.className = 'hidden';
        } else {
            tabMomo.className = 'flex-1 py-4 text-sm font-bold text-[#2563EB] border-b-2 border-[#2563EB] bg-blue-50/50 transition';
            tabPaystack.className = 'flex-1 py-4 text-sm font-bold text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition';
            contentMomo.className = 'block';
            contentPaystack.className = 'hidden';
        }
    }
</script>
@endpush
@endsection