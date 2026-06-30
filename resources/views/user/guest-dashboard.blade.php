@extends('layouts.user')
@section('title', 'Dashboard')
@section('page-title', 'Guest Dashboard')
@section('page-description', 'Your order summary')

@section('content')
<div class="space-y-5">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl p-4">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center mb-3">
                <x-heroicon-o-shopping-bag class="w-4 h-4 text-blue-500" />
            </div>
            <p class="text-2xl font-black text-slate-800">{{ $totalOrders }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Total Orders</p>
        </div>
        <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl p-4">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
                <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-500" />
            </div>
            <p class="text-2xl font-black text-slate-800">{{ $completedOrders }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Delivered</p>
        </div>
        <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl p-4">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center mb-3">
                <x-heroicon-o-clock class="w-4 h-4 text-amber-500" />
            </div>
            <p class="text-2xl font-black text-slate-800">{{ $pendingOrders }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Pending</p>
        </div>
        <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl p-4">
            <div class="w-9 h-9 rounded-xl bg-[#EA580C]/10 flex items-center justify-center mb-3">
                <x-heroicon-o-banknotes class="w-4 h-4 text-[#EA580C]" />
            </div>
            <p class="text-2xl font-black text-slate-800">GH&#8373;{{ number_format($totalSpent, 2) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Total Spent</p>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Recent Orders</h3>
        </div>
        @if($orders->isEmpty())
            <div class="px-5 py-10 text-center">
                <x-heroicon-o-inbox class="w-10 h-10 text-slate-300 mx-auto mb-3" />
                <p class="text-sm text-slate-400">No orders yet</p>
                <a href="{{ route('user.buy-data') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-[#EA580C] hover:underline">
                    Place your first order <x-heroicon-o-arrow-right class="w-3 h-3" />
                </a>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($orders as $order)
                    <div class="px-5 py-3.5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                {{ strtoupper(substr($order->network_type, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800">{{ $order->package_size }} &middot; {{ $order->network_type }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $order->phone_number }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-slate-800">GH&#8373;{{ number_format($order->amount, 2) }}</p>
                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wide
                                {{ in_array($order->status, ['completed', 'delivered']) ? 'bg-emerald-50 text-emerald-600' :
                                   ($order->status === 'failed' ? 'bg-red-50 text-red-600' :
                                   ($order->status === 'cancelled' ? 'bg-slate-100 text-slate-500' : 'bg-amber-50 text-amber-600')) }}">
                                {{ $order->status }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
