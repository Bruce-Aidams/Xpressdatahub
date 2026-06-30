¿@extends('layouts.admin')
@section('page-title', 'Shop Orders')
@section('page-description', 'Verify shop orders')
@section('content')
<div class="bg-white border border-slate-100 shadow-sm rounded-2xl overflow-hidden">
    <div class="p-5 border-b border-slate-100">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders..." class="flex-1 min-w-[200px] px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#2563EB] outline-none">
            <select name="status" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] outline-none">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Completed</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-medium rounded-xl transition"><x-heroicon-o-magnifying-glass class="w-5 h-5" /></button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-100">
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">ID</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Shop</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">User</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Amount</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Status</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Actions</th>
            </tr></thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b border-slate-100 hover:bg-blue-50/20">
                        <td class="px-5 py-3 text-slate-800">#{{ $order->id }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $order->shop->name ?? 'N/A' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $order->agent->username ?? 'N/A' }}</td>
                        <td class="px-5 py-3 text-slate-600">GHâ‚µ{{ number_format($order->amount, 2) }}</td>
                        <td class="px-5 py-3">
                            <x-status-badge :status="$order->status" />
                        </td>
                        <td class="px-5 py-3">
                            @if($order->status === 'pending')
                                <form method="POST" action="{{ route('admin.shop-orders.verify') }}" class="inline">@csrf <input type="hidden" name="order_id" value="{{ $order->id }}">
                                    <button type="submit" class="text-emerald-500 hover:text-emerald-600 text-xs"><x-heroicon-o-check class="w-4 h-4" />Verify</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500 text-sm">No shop orders found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-100">{{ $orders->withQueryString()->links('pagination::tailwind') }}</div>
</div>
@endsection
