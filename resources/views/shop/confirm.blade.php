@extends('layouts.app')
@section('title', 'Order Confirmation')
@section('body')
<div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
            {{-- Status Banner --}}
            @php
                $status = $order->status ?? 'pending';
                $statusConfig = match($status) {
                    'delivered' => ['bg' => 'bg-emerald-500', 'icon' => 'heroicon-o-check-circle', 'label' => 'Delivered', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'bgLight' => 'bg-emerald-50'],
                    'processing' => ['bg' => 'bg-blue-500', 'icon' => 'heroicon-o-arrow-path', 'label' => 'Processing', 'text' => 'text-blue-600', 'border' => 'border-blue-200', 'bgLight' => 'bg-blue-50'],
                    'pending' => ['bg' => 'bg-amber-500', 'icon' => 'heroicon-o-clock', 'label' => 'Pending', 'text' => 'text-amber-600', 'border' => 'border-amber-200', 'bgLight' => 'bg-amber-50'],
                    'failed' => ['bg' => 'bg-red-500', 'icon' => 'heroicon-o-x-circle', 'label' => 'Failed', 'text' => 'text-red-600', 'border' => 'border-red-200', 'bgLight' => 'bg-red-50'],
                    'cancelled' => ['bg' => 'bg-slate-500', 'icon' => 'heroicon-o-no-symbol', 'label' => 'Cancelled', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'bgLight' => 'bg-slate-50'],
                    default => ['bg' => 'bg-slate-500', 'icon' => 'heroicon-o-question-mark-circle', 'label' => ucfirst($status), 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'bgLight' => 'bg-slate-50'],
                };
            @endphp

            <div class="p-6 text-center border-b border-slate-100">
                <div class="w-16 h-16 rounded-2xl {{ $statusConfig['bgLight'] }} flex items-center justify-center mx-auto mb-4">
                    <x-dynamic-component :component="$statusConfig['icon']" class="w-8 h-8 {{ $statusConfig['text'] }}" />
                </div>
                <h1 class="text-xl font-black text-slate-800">Order {{ $statusConfig['label'] }}</h1>
                <p class="text-sm text-slate-400 mt-1">Reference: {{ $order->order_reference ?? 'N/A' }}</p>
            </div>

            {{-- Order Details --}}
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-400">Phone Number</span>
                    <span class="text-sm font-bold text-slate-800">{{ $order->phone_number ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-400">Network</span>
                    <span class="text-sm font-bold text-slate-800">{{ $order->network_type ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-400">Package</span>
                    <span class="text-sm font-bold text-slate-800">{{ $order->package_size ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-400">Amount Paid</span>
                    <span class="text-sm font-bold text-[#FF7A00]">GH₵{{ number_format($order->amount ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-400">Date</span>
                    <span class="text-sm text-slate-600">{{ $order->created_at?->format('M d, Y h:i A') ?? 'N/A' }}</span>
                </div>

                @if($status === 'pending' || $status === 'processing')
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl">
                        <p class="text-xs text-blue-600 flex items-center gap-1.5">
                            <x-heroicon-o-information-circle class="w-4 h-4 shrink-0" />
                            Your data is being processed. It will be delivered shortly.
                        </p>
                    </div>
                @endif

                @if($status === 'delivered')
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                        <p class="text-xs text-emerald-600 flex items-center gap-1.5">
                            <x-heroicon-o-check-circle class="w-4 h-4 shrink-0" />
                            Data has been delivered to {{ $order->phone_number }}. Please check your phone.
                        </p>
                    </div>
                @endif

                @if($status === 'failed')
                    <div class="p-3 bg-red-50 border border-red-200 rounded-xl">
                        <p class="text-xs text-red-600 flex items-center gap-1.5">
                            <x-heroicon-o-x-circle class="w-4 h-4 shrink-0" />
                            Data delivery failed. Please contact support for assistance.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-6 pb-6">
                @if($shop)
                    <a href="{{ route('shop.show', $shop->shop_slug) }}"
                       class="block w-full text-center bg-[#FF7A00] hover:bg-[#E06B00] text-white text-sm font-bold rounded-xl px-4 py-2.5 transition shadow-md shadow-orange-500/10">
                        Back to {{ $shop->name }}
                    </a>
                @else
                    <p class="text-center text-xs text-slate-400">Powered by <span class="text-[#FF7A00] font-bold">Xpressdatahub</span></p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
