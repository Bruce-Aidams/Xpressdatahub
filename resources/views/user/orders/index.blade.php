�@extends('layouts.user')

@section('title', 'Order History')

@section('content')

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Order History</h1>
        <p class="text-sm text-slate-400 mt-1">View all your past orders</p>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 shadow-sm mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-[220px]">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by ID or phone..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#EA580C] focus:ring-0 outline-none transition"
                />
            </div>

            <select
                name="status"
                class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] focus:ring-0 outline-none transition"
            >
                <option value="">All Status</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Completed</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>

            <button
                type="submit"
                class="bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-bold rounded-xl px-4 py-2 transition"
            >
                Filter
            </button>
        </form>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Order ID</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Phone</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Network</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Package</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Amount (GH₵)</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-orange-50/20 transition">
                            <td class="px-5 py-3 font-semibold text-slate-800">#{{ $order->id }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $order->phone_number }}</td>
                            <td class="px-5 py-3"><x-network-badge :network="$order->network_type" /></td>
                            <td class="px-5 py-3 text-slate-600">{{ $order->package_size ?? 'N/A' }}</td>
                            <td class="px-5 py-3 font-medium text-slate-700">GH₵{{ number_format($order->amount, 2) }}</td>
                            <td class="px-5 py-3"><x-status-badge :status="$order->status" /></td>
                            <td class="px-5 py-3 text-slate-400 text-xs">{{ $order->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <x-heroicon-o-inbox class="w-12 h-12 text-slate-300" />
                                    <p class="text-sm font-medium text-slate-400">No orders found</p>
                                    <p class="text-xs text-slate-300">Your order history will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/30">
                {{ $orders->withQueryString()->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

@endsection
