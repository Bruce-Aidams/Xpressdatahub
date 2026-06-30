@extends('layouts.user')
@section('title', 'My Cart')
@section('page-title', 'Shopping Cart')
@section('page-description', 'Review and manage your data orders')
@section('content')

<div class="space-y-6">
    @if($cartItems->isEmpty())
        {{-- Empty Cart --}}
        <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <x-heroicon-o-shopping-cart class="w-8 h-8 text-slate-300" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Your cart is empty</h3>
            <p class="text-sm text-slate-400 mb-5">Add data packages to get started</p>
            <a href="{{ route('user.buy-data') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#EA580C] hover:bg-[#C2410C] text-white font-bold text-sm rounded-xl shadow-md shadow-orange-500/10 transition">
                <x-heroicon-o-plus class="w-4 h-4" /> Browse Packages
            </a>
        </div>
    @else
        {{-- Cart Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">{{ $cartItems->count() }} item(s) in cart</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Review your selections before checkout</p>
            </div>
            <form method="POST" action="{{ route('user.cart.clear') }}" onsubmit="return confirm('Clear all items from cart?')">
                @csrf
                <button type="submit" class="text-[11px] font-bold text-red-500 hover:text-red-600 transition flex items-center gap-1">
                    <x-heroicon-o-trash class="w-3.5 h-3.5" /> Clear All
                </button>
            </form>
        </div>

        {{-- Cart Items --}}
        <div class="space-y-3">
            @foreach($cartItems as $item)
                @php
                    $netColors = match($item->network_type) {
                        'MTN' => 'bg-amber-50 border-amber-200/50',
                        'Telecel' => 'bg-red-50 border-red-200/50',
                        'AirtelTigo' => 'bg-blue-50 border-blue-200/50',
                        default => 'bg-slate-50 border-slate-200'
                    };
                    $netBadge = match($item->network_type) {
                        'MTN' => 'bg-amber-100 text-amber-700',
                        'Telecel' => 'bg-red-100 text-red-700',
                        'AirtelTigo' => 'bg-blue-100 text-blue-700',
                        default => 'bg-slate-100 text-slate-600'
                    };
                @endphp
                <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl overflow-hidden">
                    {{-- Item Header --}}
                    <div class="px-5 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $netColors }} border flex items-center justify-center">
                                <x-heroicon-o-signal class="w-5 h-5 {{ match($item->network_type) { 'MTN' => 'text-amber-600', 'Telecel' => 'text-red-600', 'AirtelTigo' => 'text-blue-600', default => 'text-slate-500' } }}" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-800">{{ $item->package_size }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold {{ $netBadge }}">{{ $item->network_type }}</span>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-0.5">Phone: {{ $item->phone_number ?? 'Not set' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-black text-slate-800">GH&#8373;{{ number_format($item->amount * $item->quantity, 2) }}</p>
                            @if($item->quantity > 1)
                                <p class="text-[10px] text-slate-400">GH&#8373;{{ number_format($item->amount, 2) }} each</p>
                            @endif
                        </div>
                    </div>

                    {{-- Edit Form --}}
                    <div class="px-5 py-3 bg-slate-50/50 border-t border-slate-100">
                        <form method="POST" action="{{ route('user.cart.update', $item) }}" class="flex items-end gap-3">
                            @csrf
                            @method('PUT')
                            <div class="flex-1">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Phone</label>
                                <div class="relative">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                        <span class="text-[10px] font-bold text-slate-500">+233</span>
                                        <div class="w-px h-3 bg-slate-300"></div>
                                    </div>
                                    <input type="tel" name="phone_number" value="{{ $item->phone_number }}" maxlength="10" required
                                           class="w-full pl-12 pr-3 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-800 focus:border-[#EA580C] focus:ring-0 outline-none transition">
                                </div>
                            </div>
                            <div class="w-20">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Qty</label>
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="10" required
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-800 text-center focus:border-[#EA580C] focus:ring-0 outline-none transition">
                            </div>
                            <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-bold transition shrink-0">
                                Update
                            </button>
                        </form>
                        <form method="POST" action="{{ route('user.cart.destroy', $item) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[11px] font-bold text-red-500 hover:text-red-600 transition flex items-center gap-1">
                                <x-heroicon-o-trash class="w-3.5 h-3.5" /> Remove
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Summary & Checkout --}}
        <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-5 py-4 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Items</span>
                    <span class="font-bold text-slate-700">{{ $cartItems->count() }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Wallet Balance</span>
                    <span class="font-bold {{ $agent->balance >= $total ? 'text-emerald-600' : 'text-red-500' }}">GH&#8373;{{ number_format($agent->balance ?? 0, 2) }}</span>
                </div>
                <div class="border-t border-slate-100 pt-3 flex justify-between items-end">
                    <span class="text-sm font-bold text-slate-700">Total Amount</span>
                    <span class="text-2xl font-black text-[#EA580C]">GH&#8373;{{ number_format($total, 2) }}</span>
                </div>
            </div>
            <div class="px-5 pb-5">
                @if($agent->balance < $total)
                    <div class="bg-red-50 border border-red-200/60 rounded-xl px-4 py-3 mb-3">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-exclamation-circle class="text-red-500 w-4 h-4 shrink-0" />
                            <p class="text-[11px] font-medium text-red-600">Insufficient balance. You need GH&#8373;{{ number_format($total - $agent->balance, 2) }} more.</p>
                        </div>
                        <a href="{{ route('user.wallet.topup') }}" class="mt-2 inline-flex items-center gap-1 text-[11px] font-bold text-[#EA580C] hover:underline">
                            Top Up Wallet <x-heroicon-o-arrow-right class="w-3 h-3" />
                        </a>
                    </div>
                @endif
                <form method="POST" action="{{ route('user.cart.checkout') }}" onsubmit="return confirm('Place all {{ $cartItems->count() }} order(s) for GH₵{{ number_format($total, 2) }}?')">
                    @csrf
                    <button type="submit" {{ $agent->balance < $total ? 'disabled' : '' }}
                            class="w-full py-3.5 bg-[#EA580C] hover:bg-[#C2410C] disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/20 transition-all duration-200 flex items-center justify-center gap-2 active:scale-[0.98]">
                        <x-heroicon-o-credit-card class="w-4 h-4" />
                        <span>Place All Orders</span>
                    </button>
                </form>
                <a href="{{ route('user.buy-data') }}" class="mt-3 w-full inline-flex items-center justify-center gap-1 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-700 transition">
                    <x-heroicon-o-plus class="w-4 h-4" /> Add More Packages
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
