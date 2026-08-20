@extends('layouts.admin')
@section('page-title', 'Order Detail')
@section('page-description', '#' . ($order->id ?? ''))
@section('content')

<div class="max-w-5xl space-y-6">

    {{-- Breadcrumb --}}
    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-[#2563EB] transition">
        <x-heroicon-o-arrow-left class="w-5 h-5" /> Back to Orders
    </a>

    {{-- Top Status Row --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-[#2563EB]/10 flex items-center justify-center">
                <x-heroicon-o-shopping-bag class="w-5 h-5" />
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Order ID</p>
                <h2 class="text-xl font-black text-slate-800">#{{ $order->id }}</h2>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @php $s = $order->status; @endphp
            <x-status-badge :status="$s" />
            <button onclick="document.getElementById('deleteOrderModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-xl border border-red-100 transition">
                <x-heroicon-o-trash class="w-4 h-4" />
                Delete
            </button>
        </div>
    </div>

    {{-- Two column detail --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Product info --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <x-heroicon-o-device-phone-mobile class="w-5 h-5" /> Data Bundle Details
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Network</p>
                    <div class="mt-1"><x-network-badge :network="$order->network_type" /></div>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Package</p>
                    <p class="text-sm font-bold text-slate-700 mt-0.5">{{ $order->package_size ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Phone Number</p>
                    <p class="text-sm font-bold text-slate-700 mt-0.5">{{ $order->phone_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Amount</p>
                    <p class="text-sm font-black text-[#2563EB] mt-0.5">GH&#8373;{{ number_format($order->amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Base Amount</p>
                    <p class="text-sm font-bold text-slate-600 mt-0.5">GH&#8373;{{ number_format($order->base_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Payment Method</p>
                    <p class="text-sm font-bold text-slate-600 mt-0.5 capitalize">{{ $order->payment_method ?? 'N/A' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Transaction ID</p>
                    <p class="text-sm font-mono font-semibold text-slate-600 mt-0.5">{{ $order->transaction_id ?? 'N/A' }}</p>
                </div>
                @if($order->order_reference)
                <div class="col-span-2">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Order Reference</p>
                    <p class="text-sm font-mono font-semibold text-slate-600 mt-0.5">{{ $order->order_reference }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <x-heroicon-o-user class="w-5 h-5" /> Customer Info
            </h3>
            <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-xl">
                @if($order->guest_id)
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-black text-sm">
                        {{ strtoupper(substr($order->guest_id, 0, 2)) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">{{ $order->guest_id }}</p>
                        <p class="text-xs text-slate-500">Guest User</p>
                    </div>
                @else
                    <div class="w-10 h-10 rounded-full bg-[#2563EB]/10 flex items-center justify-center text-[#2563EB] font-black text-sm">
                        {{ strtoupper(substr($order->agent->username ?? 'U', 0, 2)) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">{{ $order->agent->username ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-500">{{ $order->agent->email ?? '' }}</p>
                    </div>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-4 mt-2">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Order Date</p>
                    <p class="text-sm font-bold text-slate-700 mt-0.5">{{ $order->created_at?->format('M d, Y') ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Time</p>
                    <p class="text-sm font-bold text-slate-700 mt-0.5">{{ $order->created_at?->format('H:i:s') ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Last Updated</p>
                    <p class="text-sm font-bold text-slate-700 mt-0.5">{{ $order->updated_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Source</p>
                    <p class="text-sm font-bold text-slate-700 mt-0.5 capitalize">{{ $order->order_source ?? 'agent' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Status --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <x-heroicon-o-pencil class="w-5 h-5" /> Update Order Status
        </h3>
        <form method="POST" action="{{ route('admin.orders.status', $order->id) }}" class="flex flex-col sm:flex-row items-end gap-3">
            @csrf
            @method('PUT')
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] outline-none transition">
                    @foreach(['pending', 'processing', 'delivered', 'failed', 'cancelled'] as $st)
                        <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Notes (optional)</label>
                <input type="text" name="notes" placeholder="Reason for status change..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] outline-none transition">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl shadow-sm transition-all whitespace-nowrap">
                <x-heroicon-o-arrow-up-tray class="w-5 h-5" /> Update
            </button>
        </form>
    </div>

    {{-- Status History --}}
    @if($order->statusHistory->count())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <x-heroicon-o-clock class="w-5 h-5" /> Status History
        </h3>
        <div class="space-y-3">
            @foreach($order->statusHistory->sortByDesc('created_at') as $history)
                <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl">
                    <div class="w-8 h-8 rounded-full bg-[#2563EB]/10 flex items-center justify-center shrink-0 mt-0.5">
                        <x-heroicon-o-arrows-right-left class="text-[#2563EB] w-4 h-4" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-slate-600 capitalize">{{ $history->old_status }}</span>
                            <x-heroicon-o-arrow-right class="w-5 h-5" />
                            <span class="text-xs font-bold text-[#2563EB] capitalize">{{ $history->new_status }}</span>
                        </div>
                        @if($history->notes)
                            <p class="text-[11px] text-slate-500 mt-1">{{ $history->notes }}</p>
                        @endif
                        <p class="text-[10px] text-slate-400 mt-1">{{ $history->created_at?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteOrderModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md mx-4 p-7">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-500" />
            </div>
            <div>
                <h3 class="text-base font-black text-slate-800">Delete Order #{{ $order->id }}?</h3>
                <p class="text-xs text-slate-400 mt-0.5">This action is permanent and cannot be undone.</p>
            </div>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-3 mb-5 text-xs text-red-600 font-medium space-y-1">
            <p>• All status history for this order will also be deleted.</p>
            <p>• The agent's wallet balance will <strong>not</strong> be refunded automatically.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="document.getElementById('deleteOrderModal').classList.add('hidden')"
                    class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                Cancel
            </button>
            <form method="POST" action="{{ route('admin.orders.destroy', $order->id) }}" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl transition">
                    Yes, Delete Order
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
