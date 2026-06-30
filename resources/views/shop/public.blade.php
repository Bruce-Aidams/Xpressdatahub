@extends('layouts.app')
@section('title', $shop->name ?? 'Shop')
@section('body')
<div class="min-h-screen bg-slate-50">
    {{-- Header --}}
    <div class="bg-white border-b border-slate-100">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#FF7A00]/10 flex items-center justify-center">
                        <x-heroicon-o-building-storefront class="text-[#FF7A00] w-5 h-5" />
                    </div>
                    <div>
                        <h1 class="text-lg font-black text-slate-800">{{ $shop->name ?? 'Shop' }}</h1>
                        @if($shop->setting && $shop->setting->whatsapp_number)
                            <p class="text-[10px] text-slate-400">WhatsApp: {{ $shop->setting->whatsapp_number }}</p>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400">Powered by <span class="font-bold text-slate-600">Xpressdata<span class="text-[#FF7A00]">hub</span></span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-600 text-sm flex items-center gap-2">
                <x-heroicon-o-check-circle class="w-4 h-4 shrink-0" />
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm flex items-center gap-2">
                <x-heroicon-o-x-circle class="w-4 h-4 shrink-0" />
                {{ session('error') }}
            </div>
        @endif

        {{-- No Refund Notice --}}
        <div class="mb-5 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5">
            <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
            <div>
                <p class="text-xs font-bold text-amber-700">Important Notice</p>
                <p class="text-[11px] text-amber-600 mt-0.5">Please double-check your phone number before ordering. <strong>No refunds</strong> are issued for data sent to wrong numbers.</p>
            </div>
        </div>

        {{-- Section Header --}}
        <div class="mb-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Available Packages</h2>
            <p class="text-[11px] text-slate-400">Select a package, enter your phone number, and pay securely via Paystack.</p>
        </div>

        {{-- Products Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($products as $product)
                <div class="bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition group">
                    {{-- Network Badge --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-[#FF7A00]/10 flex items-center justify-center">
                            <x-heroicon-o-wifi class="text-[#FF7A00] w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $product->network_type }}</p>
                        </div>
                    </div>

                    {{-- Package & Price --}}
                    <div class="mb-4">
                        <p class="text-xl font-black text-slate-800">{{ $product->package_size }}</p>
                        <p class="text-[#FF7A00] font-bold text-lg">GH₵{{ number_format($product->selling_price, 2) }}</p>
                    </div>

                    {{-- Order Form --}}
                    <form method="POST" action="{{ route('shop.order', $shop->shop_slug) }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Phone Number</label>
                            <input
                                type="tel"
                                name="phone"
                                placeholder="e.g. 0241234567"
                                required
                                minlength="10"
                                maxlength="15"
                                pattern="[0-9]+"
                                title="Enter a valid phone number (10-15 digits)"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#FF7A00] focus:ring-2 focus:ring-[#FF7A00]/10 outline-none transition"
                            />
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-[#FF7A00] hover:bg-[#E06B00] text-white text-sm font-bold rounded-xl px-4 py-2.5 transition shadow-md shadow-orange-500/10 flex items-center justify-center gap-2"
                        >
                            <x-heroicon-o-credit-card class="w-4 h-4" />
                            Pay GH₵{{ number_format($product->selling_price, 2) }}
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-span-full py-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <x-heroicon-o-building-storefront class="text-slate-400 w-8 h-8" />
                    </div>
                    <p class="text-slate-400 text-sm font-medium">No packages available</p>
                    <p class="text-slate-300 text-xs mt-1">This shop hasn't added any data packages yet.</p>
                </div>
            @endforelse
        </div>

        {{-- How It Works --}}
        @if($products->count() > 0)
            <div class="mt-8 bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">How It Works</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#FF7A00]/10 flex items-center justify-center shrink-0">
                            <span class="text-xs font-black text-[#FF7A00]">1</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-700">Select Package</p>
                            <p class="text-[11px] text-slate-400">Choose your network and data size</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#FF7A00]/10 flex items-center justify-center shrink-0">
                            <span class="text-xs font-black text-[#FF7A00]">2</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-700">Enter Phone & Pay</p>
                            <p class="text-[11px] text-slate-400">Enter your phone number and pay via Paystack</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#FF7A00]/10 flex items-center justify-center shrink-0">
                            <span class="text-xs font-black text-[#FF7A00]">3</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-700">Receive Data</p>
                            <p class="text-[11px] text-slate-400">Data is delivered instantly to your phone</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="text-center py-6 text-xs text-slate-400 border-t border-slate-100">
        <p>&copy; {{ date('Y') }} <span class="text-[#FF7A00] font-bold">Xpressdatahub</span>. All rights reserved.</p>
    </div>
</div>
@endsection
