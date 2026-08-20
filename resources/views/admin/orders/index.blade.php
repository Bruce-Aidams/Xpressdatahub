@extends('layouts.admin')
@section('page-title', 'Orders')
@section('page-description', 'All platform orders')
@section('content')

{{-- Metric Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5 mb-6 lg:mb-8">
    <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-5 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Revenue</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1">GH&#8373;{{ number_format($stats['revenue'] ?? 0, 0) }}</p>
            <div class="flex items-center gap-1 mt-1">
                <span class="text-[10px] sm:text-xs font-bold {{ $revenueChange >= 0 ? 'text-emerald-500' : 'text-red-500' }}">{{ $revenueChange >= 0 ? '+' : '' }}{{ $revenueChange }}%</span>
                <x-dynamic-component :component="$revenueChange >= 0 ? 'heroicon-o-chevron-up' : 'heroicon-o-chevron-down'" class="{{ $revenueChange >= 0 ? 'text-emerald-500' : 'text-red-500' }} text-[8px] sm:text-[9px]" />
            </div>
        </div>
        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#2563EB]/10 flex items-center justify-center shrink-0">
            <x-heroicon-o-currency-dollar class="w-5 h-5" />
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-5 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Orders</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1">{{ number_format($stats['total_orders'] ?? 0) }}</p>
            <div class="flex items-center gap-1 mt-1">
                <span class="text-[10px] sm:text-xs font-bold {{ $orderChange >= 0 ? 'text-emerald-500' : 'text-red-500' }}">{{ $orderChange >= 0 ? '+' : '' }}{{ $orderChange }}%</span>
                <x-dynamic-component :component="$orderChange >= 0 ? 'heroicon-o-chevron-up' : 'heroicon-o-chevron-down'" class="{{ $orderChange >= 0 ? 'text-emerald-500' : 'text-red-500' }} text-[8px] sm:text-[9px]" />
            </div>
        </div>
        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
            <x-heroicon-o-shopping-bag class="w-5 h-5" />
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-5 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Agents</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1">{{ number_format($stats['total_agents'] ?? 0) }}</p>
            <div class="flex items-center gap-1 mt-1">
                <span class="text-[10px] sm:text-xs font-bold {{ $agentChange >= 0 ? 'text-emerald-500' : 'text-red-500' }}">{{ $agentChange >= 0 ? '+' : '' }}{{ $agentChange }}%</span>
                <x-dynamic-component :component="$agentChange >= 0 ? 'heroicon-o-chevron-up' : 'heroicon-o-chevron-down'" class="{{ $agentChange >= 0 ? 'text-emerald-500' : 'text-red-500' }} text-[8px] sm:text-[9px]" />
            </div>
        </div>
        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <x-heroicon-o-users class="w-5 h-5" />
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-5 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Shops</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1">{{ number_format($stats['active_shops'] ?? 0) }}</p>
            <div class="flex items-center gap-1 mt-1">
                <span class="text-[10px] sm:text-xs font-bold text-[#2563EB]">{{ $shopChange }}% active</span>
                <x-heroicon-o-chevron-up class="w-5 h-5" />
            </div>
        </div>
        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
            <x-heroicon-o-building-storefront class="w-5 h-5" />
        </div>
    </div>
</div>

{{-- Orders Update Line Chart Card --}}
@php
    $chartWidth = 700;
    $chartHeight = 160;
    $padding = 30;
    $points = $hourlyOrders->map(function ($d, $i) use ($maxHourly, $chartWidth, $chartHeight, $padding, $hourlyOrders) {
        $total = count($hourlyOrders);
        $x = $padding + ($i / max($total - 1, 1)) * ($chartWidth - $padding * 2);
        $y = $maxHourly > 0 ? $chartHeight - ($d['count'] / $maxHourly) * ($chartHeight - 30) : $chartHeight / 2;
        return ['x' => $x, 'y' => $y, 'count' => $d['count'], 'label' => $d['label']];
    });
    $linePath = $points->map(fn($p, $i) => ($i === 0 ? 'M' : 'L') . ' ' . round($p['x']) . ' ' . round($p['y']))->implode(' ');
    $areaPath = $linePath . ' L ' . round($points->last()['x']) . ' ' . $chartHeight . ' L ' . round($points->first()['x']) . ' ' . $chartHeight . ' Z';
    $peak = $points->sortByDesc('count')->first();
    $labelStep = max(1, intdiv(count($points), 6));
@endphp
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-6 lg:mb-8">
    <div class="flex items-center justify-between mb-4 sm:mb-5">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-[#2563EB] inline-block"></span>
            <span class="text-xs sm:text-sm font-bold text-slate-700">Orders Update</span>
        </div>
        <a href="{{ route('admin.analytics.index') }}" class="text-[10px] sm:text-xs font-semibold text-slate-500 hover:text-[#2563EB] transition flex items-center gap-1">
            View Details <x-heroicon-o-chevron-right class="w-5 h-5" />
        </a>
    </div>
    <div class="relative h-44 sm:h-56">
        <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight + 25 }}" class="w-full h-full" preserveAspectRatio="xMidYMid meet">
            <defs>
                <linearGradient id="orderGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#2563EB" stop-opacity="0.15"/>
                    <stop offset="100%" stop-color="#2563EB" stop-opacity="0.01"/>
                </linearGradient>
            </defs>
            @for($i = 0; $i <= 4; $i++)
                <line x1="{{ $padding }}" y1="{{ 10 + ($i * ($chartHeight - 10) / 4) }}" x2="{{ $chartWidth - $padding }}" y2="{{ 10 + ($i * ($chartHeight - 10) / 4) }}" stroke="#f1f5f9" stroke-width="1"/>
            @endfor
            <path d="{{ $areaPath }}" fill="url(#orderGrad)" stroke="none"/>
            <path d="{{ $linePath }}" fill="none" stroke="#2563EB" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            @foreach($points as $p)
                <circle cx="{{ round($p['x']) }}" cy="{{ round($p['y']) }}" r="3" fill="white" stroke="#2563EB" stroke-width="2"/>
            @endforeach
            @if($peak)
                <circle cx="{{ round($peak['x']) }}" cy="{{ round($peak['y']) }}" r="5" fill="#2563EB"/>
                <rect x="{{ round($peak['x']) - 20 }}" y="{{ round($peak['y']) - 26 }}" width="40" height="18" rx="9" fill="#2563EB"/>
                <text x="{{ round($peak['x']) }}" y="{{ round($peak['y']) - 14 }}" text-anchor="middle" fill="white" font-size="9" font-weight="700">{{ $peak['count'] }}</text>
            @endif
            @foreach($points as $i => $p)
                @if($i % $labelStep === 0 || $i === count($points) - 1)
                    <text x="{{ round($p['x']) }}" y="{{ $chartHeight + 20 }}" text-anchor="middle" fill="#94a3b8" font-size="9">{{ $p['label'] }}h</text>
                @endif
            @endforeach
            @for($i = 0; $i <= 4; $i++)
                @php $val = round($maxHourly * (4 - $i) / 4); @endphp
                <text x="8" y="{{ 14 + ($i * ($chartHeight - 10) / 4) }}" fill="#94a3b8" font-size="9" text-anchor="start">{{ $val }}</text>
            @endfor
        </svg>
    </div>
</div>

{{-- Bulk Action Bar --}}
<div id="bulkActionBar" class="hidden bg-white border border-slate-100/80 rounded-2xl shadow-sm mb-4 px-4 sm:px-6 py-3">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-[#2563EB]/10 flex items-center justify-center">
                <span id="selectedCount" class="text-[#2563EB] text-xs font-black">0</span>
            </div>
            <span class="text-sm font-semibold text-slate-600">selected</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="openBulkStatusModal()" class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                Update Status
            </button>
            <button onclick="openBulkDeleteModal()" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <x-heroicon-o-trash class="w-3.5 h-3.5" />
                Delete Selected
            </button>
            <button onclick="clearSelection()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                Deselect
            </button>
        </div>
    </div>
</div>

{{-- Orders Table --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    {{-- Table Header / Filters --}}
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
        <div>
            <h3 class="text-xs sm:text-sm font-bold text-slate-800">Latest Orders</h3>
        </div>
        <form method="GET" class="flex flex-wrap gap-2 sm:ml-auto w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders..."
                   class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] w-full sm:w-40">
            <select name="status" class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB]">
                <option value="">All Status</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Completed</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="failed"    {{ request('status') === 'failed'    ? 'selected' : '' }}>Failed</option>
            </select>
            <select name="network_type" class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB]">
                <option value="">All Networks</option>
                <option value="MTN"       {{ request('network_type') === 'MTN'       ? 'selected' : '' }}>MTN</option>

                <option value="AirtelTigo"{{ request('network_type') === 'AirtelTigo'? 'selected' : '' }}>AirtelTigo</option>
                <option value="Telecel"   {{ request('network_type') === 'Telecel'   ? 'selected' : '' }}>Telecel</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB]">
            <button type="submit" class="px-3 sm:px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs sm:text-sm font-bold rounded-xl transition flex items-center gap-1.5">
                <x-heroicon-o-magnifying-glass class="w-5 h-5" /> <span class="sm:inline">Filter</span>
            </button>
        </form>
        <a href="{{ route('admin.orders.all') }}" class="text-[10px] sm:text-xs font-semibold text-slate-400 hover:text-[#2563EB] transition flex items-center gap-1 shrink-0">
            More <x-heroicon-o-chevron-right class="w-5 h-5" />
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-slate-50/60 border-b border-slate-100">
                    <th class="px-3 sm:px-5 py-3 w-10">
                        <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB] cursor-pointer">
                    </th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Order ID</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden sm:table-cell">Products</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden md:table-cell">Date <x-heroicon-o-chevron-up-down class="w-5 h-5" /></th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden sm:table-cell">Customer</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Amount <x-heroicon-o-chevron-up-down class="w-5 h-5" /></th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden sm:table-cell">Status <x-heroicon-o-chevron-up-down class="w-5 h-5" /></th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php $s = $order->status; @endphp
                    <tr class="border-b border-slate-100 hover:bg-blue-50/30 transition">
                        <td class="px-3 sm:px-5 py-3 sm:py-4">
                            <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="row-checkbox w-4 h-4 rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB] cursor-pointer">
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4">
                            <div class="font-black text-slate-800 text-xs sm:text-sm">#{{ $order->id }}</div>
                            <div class="text-[10px] text-slate-400 sm:hidden"><x-network-badge :network="$order->network_type ?? 'N/A'" /> {{ $order->package_size ?? '' }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5 sm:hidden">
                                @if($s === 'delivered')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                @elseif($s === 'processing')
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                @elseif($s === 'failed')
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                @endif
                                <span class="text-[10px] font-bold text-slate-500">{{ ucfirst($s) }}</span>
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
                        <td class="px-3 sm:px-5 py-3 sm:py-4 text-slate-500 text-[10px] sm:text-xs hidden md:table-cell">{{ $order->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 hidden sm:table-cell">
                            <div class="flex items-center gap-2">
                                @if($order->guest_id)
                                    <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-black text-[10px] shrink-0">
                                        {{ strtoupper(substr($order->guest_id, 0, 2)) }}
                                    </div>
                                    <span class="text-xs font-semibold text-slate-600">{{ $order->guest_id }}</span>
                                @else
                                    <div class="w-7 h-7 rounded-full bg-[#2563EB]/10 flex items-center justify-center text-[#2563EB] font-black text-[10px] shrink-0">
                                        {{ strtoupper(substr($order->agent->username ?? 'U', 0, 2)) }}
                                    </div>
                                    <span class="text-xs font-semibold text-slate-600">{{ $order->agent->username ?? 'N/A' }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 font-bold text-slate-800 text-xs sm:text-sm">GH&#8373;{{ number_format($order->amount, 2) }}</td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 hidden sm:table-cell">
                            <x-status-badge :status="$s" />
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-slate-400 hover:text-[#2563EB] transition" title="View"><x-heroicon-o-eye class="w-5 h-5" /></a>
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

{{-- Bulk Status Modal --}}
<div id="bulkStatusModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md mx-4 p-7">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-black text-slate-800">Change Order Status</h3>
                <p class="text-xs text-slate-400 mt-0.5">Update status for selected orders</p>
            </div>
            <button onclick="closeBulkStatusModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <form id="bulkStatusForm" method="POST" action="{{ route('admin.orders.bulk.status') }}" class="space-y-4">
            @csrf
            <div id="bulkStatusIdsContainer"></div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status *</label>
                <select name="status" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="delivered">Delivered</option>
                    <option value="failed">Failed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeBulkStatusModal()"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl transition">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Delete Confirmation Modal --}}
<div id="bulkDeleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md mx-4 p-7">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-500" />
            </div>
            <div>
                <h3 class="text-base font-black text-slate-800">Delete <span id="bulkDeleteCount">0</span> Order(s)?</h3>
                <p class="text-xs text-slate-400 mt-0.5">This action is permanent and cannot be undone.</p>
            </div>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-3 mb-5 text-xs text-red-600 font-medium space-y-1">
            <p>• All status history for the selected orders will also be deleted.</p>
            <p>• Agent wallet balances will <strong>not</strong> be refunded automatically.</p>
        </div>
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.orders.bulk.delete') }}" class="space-y-4">
            @csrf
            <div id="bulkDeleteIdsContainer"></div>
            <div class="flex gap-3">
                <button type="button" onclick="closeBulkDeleteModal()"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl transition">
                    Yes, Delete All
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const bulkBar = document.getElementById('bulkActionBar');
    const countEl = document.getElementById('selectedCount');
    const bulkStatusModal = document.getElementById('bulkStatusModal');

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    }

    function updateBulkBar() {
        const count = getSelectedIds().length;
        countEl.textContent = count;
        bulkBar.classList.toggle('hidden', count === 0);
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const total = checkboxes.length;
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            selectAll.checked = total > 0 && checked === total;
            selectAll.indeterminate = checked > 0 && checked < total;
            updateBulkBar();
        });
    });

    window.clearSelection = function() {
        selectAll.checked = false;
        selectAll.indeterminate = false;
        checkboxes.forEach(cb => cb.checked = false);
        updateBulkBar();
    };

    function setHiddenInputs(containerId, ids) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = id;
            container.appendChild(input);
        });
    }

    window.openBulkStatusModal = function() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        const form = document.getElementById('bulkStatusForm');
        form.action = '{{ route("admin.orders.bulk.status") }}';
        setHiddenInputs('bulkStatusIdsContainer', ids);
        bulkStatusModal.classList.remove('hidden');
    };

    window.closeBulkStatusModal = function() {
        bulkStatusModal.classList.add('hidden');
    };

    window.openBulkDeleteModal = function() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        document.getElementById('bulkDeleteCount').textContent = ids.length;
        setHiddenInputs('bulkDeleteIdsContainer', ids);
        document.getElementById('bulkDeleteModal').classList.remove('hidden');
    };

    window.closeBulkDeleteModal = function() {
        document.getElementById('bulkDeleteModal').classList.add('hidden');
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBulkStatusModal();
            closeBulkDeleteModal();
        }
    });
})();
</script>
@endpush