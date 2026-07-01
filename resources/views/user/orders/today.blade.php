@extends('layouts.user')
@section('title', "Today's Orders")
@section('page-title', "Today's Orders")
@section('page-description', "Orders placed today")
@section('content')
<div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl overflow-hidden">
    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <h3 class="text-xs sm:text-sm font-semibold text-slate-800">Today's Orders ({{ $orders->count() }})</h3>
        <div class="flex gap-2 text-[10px] sm:text-xs">
            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 font-medium">Completed: {{ $todayCompleted }}</span>
            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 font-medium">Pending: {{ $todayPending }}</span>
            <span class="px-2 py-0.5 rounded-full bg-slate-50 text-slate-600 font-medium">Total: GH&#8373;{{ number_format($todayTotal, 2) }}</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50">
                    <th class="text-left px-4 sm:px-5 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">ID</th>
                    <th class="text-left px-4 sm:px-5 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider hidden sm:table-cell">Phone</th>
                    <th class="text-left px-4 sm:px-5 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Network</th>
                    <th class="text-left px-4 sm:px-5 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Amount</th>
                    <th class="text-left px-4 sm:px-5 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider hidden md:table-cell">Status</th>
                    <th class="text-left px-4 sm:px-5 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider hidden lg:table-cell">Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b border-slate-100 hover:bg-orange-50/20 transition">
                        <td class="px-4 sm:px-5 py-3">
                            <span class="font-black text-slate-800 text-xs sm:text-sm">#{{ $order->id }}</span>
                            <div class="text-[9px] text-slate-400 sm:hidden">{{ $order->phone_number }}</div>
                            <div class="flex items-center gap-1 sm:hidden mt-0.5">
                                @if($order->status === 'delivered')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                @elseif($order->status === 'processing')
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                @elseif($order->status === 'failed')
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 sm:px-5 py-3 text-slate-600 text-xs hidden sm:table-cell">{{ $order->phone_number }}</td>
                        <td class="px-4 sm:px-5 py-3 text-xs sm:text-sm"><x-network-badge :network="$order->network_type" /></td>
                        <td class="px-4 sm:px-5 py-3 font-bold text-slate-800 text-xs sm:text-sm">GH&#8373;{{ number_format($order->amount, 2) }}</td>
                        <td class="px-4 sm:px-5 py-3 hidden md:table-cell">
                            <x-status-badge :status="$order->status" />
                        </td>
                        <td class="px-4 sm:px-5 py-3 text-slate-400 text-[10px] sm:text-xs hidden lg:table-cell">{{ $order->created_at?->format('H:i:s') ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400 text-xs sm:text-sm">No orders today</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
