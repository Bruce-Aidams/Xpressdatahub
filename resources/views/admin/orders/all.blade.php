�@extends('layouts.admin')
@section('page-title', 'All Orders')
@section('page-description', 'Complete order history across all sources')
@section('content')

{{-- Summary Stats --}}
<div class="grid grid-cols-3 gap-3 sm:gap-4 mb-5 sm:mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-5 shadow-sm">
        <p class="text-[10px] sm:text-xs text-slate-400 font-medium">Total Orders</p>
        <p class="text-base sm:text-2xl font-bold text-slate-800 mt-0.5 sm:mt-1">{{ number_format($totalCount) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-5 shadow-sm">
        <p class="text-[10px] sm:text-xs text-slate-400 font-medium">Total Revenue</p>
        <p class="text-base sm:text-2xl font-bold text-slate-800 mt-0.5 sm:mt-1">GH&#8373;{{ number_format($totalAmount, 2) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-5 shadow-sm">
        <p class="text-[10px] sm:text-xs text-slate-400 font-medium">Showing</p>
        <p class="text-base sm:text-2xl font-bold text-slate-800 mt-0.5 sm:mt-1">{{ number_format($orders->total()) }}</p>
    </div>
</div>

{{-- Orders Table --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100">
        <h3 class="text-xs sm:text-sm font-bold text-slate-800">All Orders</h3>
    </div>
    <div class="px-4 sm:px-6 py-3 border-b border-slate-100">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders..."
                   class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] w-full sm:w-40">
            <select name="status" class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB]">
                <option value="">All Status</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Completed</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
            <select name="network_type" class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB]">
                <option value="">All Networks</option>
                <option value="MTN" {{ request('network_type') === 'MTN' ? 'selected' : '' }}>MTN</option>

                <option value="AirtelTigo" {{ request('network_type') === 'AirtelTigo' ? 'selected' : '' }}>AirtelTigo</option>
                <option value="Telecel" {{ request('network_type') === 'Telecel' ? 'selected' : '' }}>Telecel</option>
            </select>
            <select name="order_source" class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB]">
                <option value="">All Sources</option>
                <option value="agent" {{ request('order_source') === 'agent' ? 'selected' : '' }}>Agent</option>
                <option value="shop" {{ request('order_source') === 'shop' ? 'selected' : '' }}>Shop</option>
                <option value="api" {{ request('order_source') === 'api' ? 'selected' : '' }}>API</option>
            </select>
            <input type="number" name="min_amount" value="{{ request('min_amount') }}" placeholder="Min"
                   class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] w-20 sm:w-28">
            <input type="number" name="max_amount" value="{{ request('max_amount') }}" placeholder="Max"
                   class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] w-20 sm:w-28">
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB]">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB]">
            <button type="submit" class="px-3 sm:px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs sm:text-sm font-bold rounded-xl transition flex items-center gap-1.5">
                <x-heroicon-o-magnifying-glass class="w-5 h-5" /> <span class="sm:inline">Filter</span>
            </button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-slate-50/60 border-b border-slate-100">
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">ID</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden sm:table-cell">Products</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden md:table-cell">Source</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden lg:table-cell">Date</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden sm:table-cell">Agent</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Amount</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden sm:table-cell">Status</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">View</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php $s = $order->status; @endphp
                    <tr class="border-b border-slate-100 hover:bg-blue-50/30 transition">
                        <td class="px-3 sm:px-5 py-3 sm:py-4">
                            <div class="font-black text-slate-800 text-xs sm:text-sm">#{{ $order->id }}</div>
                            <div class="text-[9px] text-slate-400 sm:hidden"><x-network-badge :network="$order->network_type ?? 'N/A'" /> {{ $order->package_size ?? '' }}</div>
                            <div class="flex items-center gap-1 sm:hidden mt-0.5">
                                @if($s === 'delivered')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                @elseif($s === 'processing')
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                @elseif($s === 'failed')
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 hidden sm:table-cell">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                                    <x-heroicon-o-device-phone-mobile class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-700 text-xs leading-tight"><x-network-badge :network="$order->network_type ?? 'N/A'" /> {{ $order->package_size ?? '' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $order->phone_number ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 hidden md:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold uppercase tracking-wide
                                {{ ($order->order_source ?? '') === 'shop' ? 'bg-purple-50 text-purple-600' :
                                   (($order->order_source ?? '') === 'api' ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst($order->order_source ?? 'agent') }}
                            </span>
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 text-slate-500 text-[10px] sm:text-xs hidden lg:table-cell">{{ $order->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 hidden sm:table-cell">
                            <div class="flex items-center gap-2">
                                @if($order->guest_id)
                                    <div class="text-lg rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-black text-[9px] shrink-0">
                                        {{ strtoupper(substr($order->guest_id, 0, 2)) }}
                                    </div>
                                    <span class="text-[10px] sm:text-xs font-semibold text-slate-600">{{ $order->guest_id }}</span>
                                @else
                                    <div class="text-lg rounded-full bg-[#2563EB]/10 flex items-center justify-center text-[#2563EB] font-black text-[9px] shrink-0">
                                        {{ strtoupper(substr($order->agent->username ?? 'U', 0, 2)) }}
                                    </div>
                                    <span class="text-[10px] sm:text-xs font-semibold text-slate-600">{{ $order->agent->username ?? 'N/A' }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 font-bold text-slate-800 text-xs sm:text-sm">GH&#8373;{{ number_format($order->amount, 2) }}</td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 hidden sm:table-cell">
                            <x-status-badge :status="$s" />
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-slate-400 hover:text-[#2563EB] transition" title="View">
                                <x-heroicon-o-eye class="w-5 h-5" />
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-16 text-center text-slate-400 font-medium">No orders found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-slate-100 text-xs sm:text-sm">
        {{ $orders->withQueryString()->links('pagination::tailwind') }}
    </div>
</div>
@endsection
