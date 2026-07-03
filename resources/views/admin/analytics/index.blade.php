@extends('layouts.admin')
@section('page-title', 'Sales Analytics')
@section('page-description', 'View sales analytics and charts')
@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5 sm:mb-6">
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-3 sm:p-5">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#2563EB]/10 flex items-center justify-center"><x-heroicon-o-currency-dollar class="w-5 h-5" /></div>
            <div>
                <p class="text-[10px] sm:text-xs text-slate-400 font-medium">Today's Revenue</p>
                <p class="text-lg sm:text-2xl font-bold text-slate-800 mt-0.5">GH&#8373;{{ number_format($analytics['today_revenue'] ?? 0, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-3 sm:p-5">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 flex items-center justify-center"><x-heroicon-o-shopping-bag class="w-5 h-5" /></div>
            <div>
                <p class="text-[10px] sm:text-xs text-slate-400 font-medium">Total Orders</p>
                <p class="text-lg sm:text-2xl font-bold text-slate-800 mt-0.5">{{ number_format($analytics['today_orders'] ?? 0) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-3 sm:p-5">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 flex items-center justify-center"><x-heroicon-o-calendar class="w-5 h-5" /></div>
            <div>
                <p class="text-[10px] sm:text-xs text-slate-400 font-medium">This Month</p>
                <p class="text-lg sm:text-2xl font-bold text-slate-800 mt-0.5">GH&#8373;{{ number_format($analytics['month_revenue'] ?? 0, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-3 sm:p-5">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-purple-50 flex items-center justify-center"><x-heroicon-o-chart-bar class="w-5 h-5" /></div>
            <div>
                <p class="text-[10px] sm:text-xs text-slate-400 font-medium">Success Rate</p>
                <p class="text-lg sm:text-2xl font-bold text-slate-800 mt-0.5">{{ $analytics['success_rate'] ?? 0 }}%</p>
            </div>
        </div>
    </div>
</div>

{{-- Revenue Overview Chart --}}
@php
    $chartWidth = 800;
    $chartHeight = 180;
    $padding = 40;
    $points = $chartData->map(function ($d, $i) use ($maxRevenue, $chartWidth, $chartHeight, $padding, $chartData) {
        $total = count($chartData);
        $x = $padding + ($i / max($total - 1, 1)) * ($chartWidth - $padding * 2);
        $y = $maxRevenue > 0 ? $chartHeight - ($d['revenue'] / $maxRevenue) * ($chartHeight - 30) : $chartHeight / 2;
        return ['x' => $x, 'y' => $y, 'revenue' => $d['revenue'], 'date' => $d['date']];
    });
    $linePath = $points->map(fn($p, $i) => ($i === 0 ? 'M' : 'L') . ' ' . round($p['x']) . ' ' . round($p['y']))->implode(' ');
    $areaPath = $linePath . ' L ' . round($points->last()['x']) . ' ' . $chartHeight . ' L ' . round($points->first()['x']) . ' ' . $chartHeight . ' Z';
    $peak = $points->sortByDesc('revenue')->first();
    $labelStep = max(1, intdiv(count($points), 7));
@endphp
<div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 sm:p-6 mb-5 sm:mb-6">
    <div class="flex items-center justify-between mb-4 sm:mb-5">
        <div>
            <h3 class="text-xs sm:text-sm font-bold text-slate-800">Revenue Overview</h3>
            <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Last 14 days</p>
        </div>
    </div>
    <div class="relative h-44 sm:h-56">
        <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight + 30 }}" class="w-full h-full" preserveAspectRatio="xMidYMid meet">
            <defs>
                <linearGradient id="revGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#2563EB" stop-opacity="0.15"/>
                    <stop offset="100%" stop-color="#2563EB" stop-opacity="0.01"/>
                </linearGradient>
            </defs>
            @for($i = 0; $i <= 4; $i++)
                <line x1="{{ $padding }}" y1="{{ 10 + ($i * ($chartHeight - 10) / 4) }}" x2="{{ $chartWidth - $padding }}" y2="{{ 10 + ($i * ($chartHeight - 10) / 4) }}" stroke="#f1f5f9" stroke-width="1"/>
            @endfor
            <path d="{{ $areaPath }}" fill="url(#revGrad)" stroke="none"/>
            <path d="{{ $linePath }}" fill="none" stroke="#2563EB" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            @foreach($points as $p)
                <circle cx="{{ round($p['x']) }}" cy="{{ round($p['y']) }}" r="3" fill="white" stroke="#2563EB" stroke-width="2"/>
            @endforeach
            @if($peak)
                <circle cx="{{ round($peak['x']) }}" cy="{{ round($peak['y']) }}" r="5" fill="#2563EB"/>
                <rect x="{{ round($peak['x']) - 30 }}" y="{{ round($peak['y']) - 28 }}" width="60" height="18" rx="9" fill="#2563EB"/>
                <text x="{{ round($peak['x']) }}" y="{{ round($peak['y']) - 16 }}" text-anchor="middle" fill="white" font-size="9" font-weight="700">GH&#8373;{{ number_format($peak['revenue'], 0) }}</text>
            @endif
            @foreach($points as $i => $p)
                @if($i % $labelStep === 0 || $i === count($points) - 1)
                    <text x="{{ round($p['x']) }}" y="{{ $chartHeight + 24 }}" text-anchor="middle" fill="#94a3b8" font-size="9">{{ $p['date'] }}</text>
                @endif
            @endforeach
            @for($i = 0; $i <= 4; $i++)
                @php $val = round($maxRevenue * (4 - $i) / 4); @endphp
                <text x="8" y="{{ 14 + ($i * ($chartHeight - 10) / 4) }}" fill="#94a3b8" font-size="9" text-anchor="start">{{ $val >= 1000 ? round($val/1000,1).'K' : $val }}</text>
            @endfor
        </svg>
    </div>
</div>

{{-- Revenue by Network & Top Agents --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-5 sm:mb-6">
    {{-- Revenue by Network --}}
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 sm:p-6">
        <h3 class="text-xs sm:text-sm font-bold text-slate-800 mb-3 sm:mb-4">Revenue by Network</h3>
        @php $netTotal = max($revenueByNetwork->sum('total'), 1); @endphp
        <div class="space-y-2.5 sm:space-y-3">
            @forelse($revenueByNetwork as $net)
                @php $pct = round(($net->total / $netTotal) * 100); @endphp
                <div>
                    <div class="flex justify-between text-[10px] sm:text-xs mb-1">
                        <span class="font-semibold text-slate-700"><x-network-badge :network="$net->network_type ?? 'Unknown'" /></span>
                        <span class="text-slate-500">GH&#8373;{{ number_format($net->total, 2) }} ({{ $pct }}%)</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-[#2563EB] rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-slate-400 text-xs text-center py-4">No network data yet</p>
            @endforelse
        </div>
    </div>

    {{-- Top Agents --}}
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 sm:p-6">
        <h3 class="text-xs sm:text-sm font-bold text-slate-800 mb-3 sm:mb-4">Top Agents</h3>
        <div class="space-y-2.5 sm:space-y-3">
            @forelse($topUsers as $agent)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span class="text-lg sm:w-7 sm:h-7 rounded-lg {{ $loop->iteration <= 3 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center text-[10px] sm:text-xs font-black">{{ $loop->iteration }}</span>
                        <div>
                            <p class="text-[10px] sm:text-xs font-bold text-slate-800">{{ $agent->username }}</p>
                            <p class="text-[9px] sm:text-[10px] text-slate-400">{{ $agent->orders_count ?? 0 }} orders</p>
                        </div>
                    </div>
                    <span class="text-[10px] sm:text-xs font-black text-slate-700">GH&#8373;{{ number_format($agent->orders_sum_amount ?? 0, 2) }}</span>
                </div>
            @empty
                <p class="text-slate-400 text-xs text-center py-4">No agent data yet</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Order Status Breakdown --}}
<div class="grid grid-cols-3 gap-3 sm:gap-4 mb-5 sm:mb-6">
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-3 sm:p-5">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 flex items-center justify-center"><x-heroicon-o-check-circle class="w-5 h-5" /></div>
            <div>
                <p class="text-[10px] sm:text-xs text-slate-400 font-medium">Completed</p>
                <p class="text-base sm:text-xl font-bold text-emerald-600 mt-0.5">{{ number_format($completedOrders) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-3 sm:p-5">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-50 flex items-center justify-center"><x-heroicon-o-clock class="w-5 h-5" /></div>
            <div>
                <p class="text-[10px] sm:text-xs text-slate-400 font-medium">Pending</p>
                <p class="text-base sm:text-xl font-bold text-amber-600 mt-0.5">{{ number_format($pendingOrders) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-3 sm:p-5">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-red-50 flex items-center justify-center"><x-heroicon-o-x-circle class="w-5 h-5" /></div>
            <div>
                <p class="text-[10px] sm:text-xs text-slate-400 font-medium">Failed</p>
                <p class="text-base sm:text-xl font-bold text-red-600 mt-0.5">{{ number_format($failedOrders) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
