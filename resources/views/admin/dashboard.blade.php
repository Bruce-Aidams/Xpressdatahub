�@extends('layouts.admin')
@section('page-title', 'Dashboard')
@section('page-description', 'Real-time overview of your platform performance')
@section('content')
<!-- 4 Top Metric Cards (SaaS Style) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-6 lg:mb-8">
    <!-- Card 1: Revenue -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Revenue</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1 sm:mt-2">GH₵{{ number_format($stats['revenue'] ?? 0, 2) }}</p>
            <div class="flex items-center gap-1 mt-1.5 sm:mt-2.5">
                <span class="text-[10px] sm:text-xs font-bold {{ $revenueChange >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                    {{ $revenueChange >= 0 ? '+' : '' }}{{ $revenueChange }}%
                </span>
                <x-dynamic-component :component="$revenueChange >= 0 ? 'heroicon-o-chevron-up' : 'heroicon-o-chevron-down'" class="{{ $revenueChange >= 0 ? 'text-emerald-500' : 'text-red-500' }} w-3 h-3" />
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-[#2563EB]/10 flex items-center justify-center">
            <x-heroicon-o-currency-dollar class="w-5 h-5" />
        </div>
    </div>

    <!-- Card 2: Orders -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Orders</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1 sm:mt-2">{{ number_format($stats['total_orders'] ?? 0) }}</p>
            <div class="flex items-center gap-1 mt-1.5 sm:mt-2.5">
                <span class="text-[10px] sm:text-xs font-bold {{ $orderChange >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                    {{ $orderChange >= 0 ? '+' : '' }}{{ $orderChange }}%
                </span>
                <x-dynamic-component :component="$orderChange >= 0 ? 'heroicon-o-chevron-up' : 'heroicon-o-chevron-down'" class="{{ $orderChange >= 0 ? 'text-emerald-500' : 'text-red-500' }} w-3 h-3" />
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-blue-50 flex items-center justify-center">
            <x-heroicon-o-shopping-bag class="w-5 h-5" />
        </div>
    </div>

    <!-- Card 3: Total Agents -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Agents</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1 sm:mt-2">{{ number_format($stats['total_agents'] ?? 0) }}</p>
            <div class="flex items-center gap-1 mt-1.5 sm:mt-2.5">
                <span class="text-[10px] sm:text-xs font-bold {{ $agentChange >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                    {{ $agentChange >= 0 ? '+' : '' }}{{ $agentChange }}%
                </span>
                <x-dynamic-component :component="$agentChange >= 0 ? 'heroicon-o-chevron-up' : 'heroicon-o-chevron-down'" class="{{ $agentChange >= 0 ? 'text-emerald-500' : 'text-red-500' }} w-3 h-3" />
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 flex items-center justify-center">
            <x-heroicon-o-users class="w-5 h-5" />
        </div>
    </div>

    <!-- Card 4: Active Shops -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Shops</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1 sm:mt-2">{{ number_format($stats['active_shops'] ?? 0) }}</p>
            <div class="flex items-center gap-1 mt-1.5 sm:mt-2.5">
                <span class="text-[10px] sm:text-xs font-bold text-[#2563EB]">{{ $shopChange }}% active</span>
                <x-heroicon-o-chevron-up class="w-5 h-5" />
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 flex items-center justify-center">
            <x-heroicon-o-building-storefront class="w-5 h-5" />
        </div>
    </div>
</div>

<!-- Main Row: Bar Chart & Gauge Chart -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 lg:mb-8">
    <!-- Bar Chart Card -->
    <div class="lg:col-span-2 bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Performance Trend</h3>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Weekly volume index</p>
            </div>
            <a href="{{ route('admin.analytics.index') }}" class="text-[10px] sm:text-xs font-semibold text-[#2563EB] hover:text-[#1D4ED8] transition flex items-center gap-1">
                Report <x-heroicon-o-chevron-right class="w-5 h-5" />
            </a>
        </div>
        <!-- SVG Bar Chart -->
        <div class="h-44 sm:h-64 relative px-1 sm:px-2 pb-2">
            {{-- Grid lines --}}
            <div class="absolute inset-0 flex flex-col justify-between px-1 sm:px-2 pb-6 pointer-events-none">
                @for($i = 0; $i < 4; $i++)
                    <div class="border-b border-slate-100 w-full"></div>
                @endfor
            </div>
            {{-- Bars --}}
            <div class="absolute inset-0 flex items-end justify-between gap-1.5 sm:gap-2 px-1 sm:px-2 pb-6">
                @foreach($weeklyOrders as $i => $dayData)
                    @php
                        $height = $maxWeekly > 0 ? ($dayData['count'] / $maxWeekly) * 100 : 0;
                        $isToday = $loop->last;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 sm:gap-2 h-full justify-end">
                        @if($dayData['count'] > 0)
                        <span class="text-[8px] sm:text-[10px] font-bold {{ $isToday ? 'text-[#2563EB]' : 'text-slate-400' }}">{{ $dayData['count'] }}</span>
                        @endif
                        <div class="w-full {{ $isToday ? 'bg-gradient-to-t from-[#2563EB] to-[#60A5FA] shadow-lg shadow-blue-500/15' : 'bg-gradient-to-t from-slate-200 to-slate-100 hover:from-[#2563EB]/20 hover:to-[#2563EB]/10' }} rounded-t-lg transition-all duration-500" style="height: {{ max($height, 3) }}%;"></div>
                        <span class="text-[8px] sm:text-[10px] {{ $isToday ? 'font-bold text-[#2563EB]' : 'text-slate-400 font-medium' }}">{{ $dayData['day'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Circular Progress Gauge Card -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Active Shops</h3>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Platform engagement rate</p>
            </div>
            <x-heroicon-o-information-circle class="w-5 h-5" />
        </div>
        @php
            $activePercentage = $totalShops > 0 ? round(($activeShops / $totalShops) * 100) : 0;
            $dashOffset = 251.2 - (251.2 * $activePercentage / 100);
        @endphp
        <div class="relative w-32 h-32 sm:w-40 sm:h-40 mx-auto flex items-center justify-center">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="40" stroke="#f1f5f9" stroke-width="8" fill="transparent"></circle>
                <circle cx="50" cy="50" r="40" stroke="#2563EB" stroke-width="8" fill="transparent" 
                        stroke-dasharray="251.2" stroke-dashoffset="{{ $dashOffset }}" stroke-linecap="round"></circle>
            </svg>
            <div class="absolute text-center">
                <span class="text-2xl sm:text-3xl font-black text-slate-800">{{ $activePercentage }}%</span>
                <p class="text-[8px] sm:text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Active</p>
            </div>
        </div>
        <div class="flex justify-between border-t border-slate-50 pt-3 sm:pt-4 mt-3 sm:mt-4">
            <div class="text-center flex-1 border-r border-slate-100">
                <span class="text-[10px] sm:text-xs text-slate-400">Total Shops</span>
                <p class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5">{{ number_format($totalShops) }}</p>
            </div>
            <div class="text-center flex-1">
                <span class="text-[10px] sm:text-xs text-slate-400">Active</span>
                <p class="text-xs sm:text-sm font-bold text-emerald-500 mt-0.5">{{ number_format($activeShops) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Grid Row: Donut Chart, Traffic Line Chart, Bestsellers & Forecast -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 lg:mb-8">
    <!-- Donut Chart: Network Breakdown -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Volume by Network</h3>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Order distribution</p>
            </div>
        </div>
        @php
            $networkColors = ['MTN' => '#F59E0B', 'Telecel' => '#EF4444', 'AirtelTigo' => '#3B82F6'];
            $networkLabels = ['MTN' => 'MTN', 'Telecel' => 'Telecel', 'AirtelTigo' => 'AirtelTigo'];
            $circumference = 238.7;
            $cumulativeOffset = 0;
        @endphp
        <div class="relative w-32 h-32 sm:w-36 sm:h-36 mx-auto flex items-center justify-center my-2">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                @forelse($networkStats as $net)
                    @php
                        $pct = $totalNetworkOrders > 0 ? ($net->total / $totalNetworkOrders) : 0;
                        $dashLen = $circumference * $pct;
                        $color = $networkColors[$net->network_type] ?? '#94A3B8';
                        $rotation = ($cumulativeOffset / $circumference) * 360;
                    @endphp
                    <circle cx="50" cy="50" r="38" stroke="{{ $color }}" stroke-width="10" fill="transparent"
                            stroke-dasharray="{{ $dashLen }} {{ $circumference - $dashLen }}"
                            transform="rotate({{ $rotation }} 50 50)"></circle>
                    @php $cumulativeOffset += $dashLen; @endphp
                @empty
                    <circle cx="50" cy="50" r="38" stroke="#E2E8F0" stroke-width="10" fill="transparent"></circle>
                @endforelse
            </svg>
            <div class="absolute text-center">
                <span class="text-xl sm:text-2xl font-black text-slate-800">{{ number_format($totalNetworkOrders) }}</span>
                <p class="text-[8px] sm:text-[9px] text-slate-400 font-bold uppercase">Orders</p>
            </div>
        </div>
        <div class="space-y-1.5 mt-3 sm:mt-4 text-[10px] sm:text-xs font-semibold text-slate-600">
            @forelse($networkStats as $net)
                @php
                    $pct = $totalNetworkOrders > 0 ? round(($net->total / $totalNetworkOrders) * 100) : 0;
                    $color = $networkColors[$net->network_type] ?? '#94A3B8';
                @endphp
                <div class="flex justify-between items-center">
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $color }}"></span>
                        {{ $net->network_type }}
                    </span>
                    <span>{{ $pct }}%</span>
                </div>
            @empty
                <div class="text-center text-slate-400 py-2">No network data yet</div>
            @endforelse
        </div>
    </div>

    <!-- Traffic and Sales Forecast Cards -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Quick Actions</h3>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Common operations</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:gap-3 flex-1 items-center">
            <a href="{{ route('admin.agents.index') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-users class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-700">Agents</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-shopping-cart class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-700">Orders</span>
            </a>
            <a href="{{ route('admin.pricing.index') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-tag class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-700">Pricing</span>
            </a>
            <a href="{{ route('admin.analytics.index') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-500 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-chart-bar class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-700">Analytics</span>
            </a>
        </div>
    </div>

    <!-- Bestsellers: Package Breakdown -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col justify-between sm:col-span-2 lg:col-span-1">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Top Packages</h3>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Highly ordered data bundles</p>
            </div>
            <x-heroicon-o-star class="w-5 h-5" />
        </div>
        <div class="space-y-3 sm:space-y-4 flex-1 justify-center flex flex-col">
            @forelse($topPackages as $pkg)
                <div class="flex items-center justify-between {{ !$loop->last ? 'border-b border-slate-50 pb-2' : '' }}">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span class="text-lg sm:w-7 sm:h-7 rounded-lg {{ $loop->iteration === 1 ? 'bg-amber-100 text-amber-700' : ($loop->iteration === 2 ? 'bg-slate-100 text-slate-700' : 'bg-orange-100 text-orange-700') }} flex items-center justify-center text-[10px] sm:text-xs font-black">{{ $loop->iteration }}</span>
                        <div>
                            <p class="text-[10px] sm:text-xs font-bold text-slate-800">{{ $pkg->network_type }} {{ $pkg->package_size }}</p>
                            <p class="text-[9px] sm:text-[10px] text-slate-400">Data Bundle</p>
                        </div>
                    </div>
                    <span class="text-[10px] sm:text-xs font-black text-slate-700 whitespace-nowrap">{{ number_format($pkg->total) }} Sold</span>
                </div>
            @empty
                <div class="text-center text-slate-400 py-4 text-xs">No package data yet</div>
            @endforelse
        </div>
    </div>
</div>

<!-- Row 3: Recent Agents -->
<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden mb-6 lg:mb-8">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Recent Agents</h3>
            <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Newly registered agents</p>
        </div>
        <a href="{{ route('admin.agents.index') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 text-[10px] sm:text-xs font-bold rounded-xl transition flex items-center gap-1.5 sm:gap-2">
            View All <x-heroicon-o-arrow-top-right-on-square class="w-5 h-5" />
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50">
                    <th class="px-4 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Agent</th>
                    <th class="px-4 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider hidden sm:table-cell">Email</th>
                    <th class="px-4 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider hidden md:table-cell">Phone</th>
                    <th class="px-4 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Role</th>
                    <th class="px-4 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Status</th>
                    <th class="px-4 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider hidden lg:table-cell">Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentUsers ?? [] as $u)
                    @php $st = $u->status ?? 'active'; @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition">
                        <td class="px-4 sm:px-6 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#2563EB] to-[#60A5FA] flex items-center justify-center text-white font-bold text-[10px]">
                                    {{ strtoupper(substr($u->first_name ?? $u->username ?? 'U', 0, 1)) }}{{ strtoupper(substr($u->last_name ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-800 text-xs sm:text-sm">{{ $u->first_name ?? '' }} {{ $u->last_name ?? '' }}</span>
                                    <p class="text-[10px] text-slate-400">@{{ $u->username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-3 hidden sm:table-cell text-slate-500 text-xs">{{ $u->email ?? 'N/A' }}</td>
                        <td class="px-4 sm:px-6 py-3 hidden md:table-cell text-slate-500 text-xs">{{ $u->phone ?? 'N/A' }}</td>
                        <td class="px-4 sm:px-6 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold uppercase tracking-wide bg-slate-100 text-slate-600">{{ ucfirst($u->role ?? 'agent') }}</span>
                        </td>
                        <td class="px-4 sm:px-6 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold uppercase tracking-wide
                                {{ $st === 'active' ? 'bg-emerald-50 text-emerald-600' : ($st === 'suspended' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600') }}">
                                {{ ucfirst($st) }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 hidden lg:table-cell text-slate-400 text-[10px] sm:text-xs">{{ $u->created_at?->diffForHumans() ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">No agents yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Row 4: Latest Orders Card -->
<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Latest Orders</h3>
            <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Most recent customer activities</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 text-[10px] sm:text-xs font-bold rounded-xl transition flex items-center gap-1.5 sm:gap-2">
            View All <x-heroicon-o-arrow-top-right-on-square class="w-5 h-5" />
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50">
                    <th class="px-4 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">ID</th>
                    <th class="px-4 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider hidden sm:table-cell">Agent</th>
                    <th class="px-4 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Network</th>
                    <th class="px-4 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Amount</th>
                    <th class="px-4 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider hidden md:table-cell">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders ?? [] as $order)
                    @php $status = $order->status ?? 'pending'; @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition">
                        <td class="px-4 sm:px-6 py-3 font-black text-slate-800 text-xs sm:text-sm">#{{ $order->id }}</td>
                        <td class="px-4 sm:px-6 py-3 hidden sm:table-cell">
                            <div class="flex items-center gap-2.5">
                                @if($order->guest_id)
                                    <div class="text-lg rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-black text-[10px]">
                                        {{ strtoupper(substr($order->guest_id, 0, 2)) }}
                                    </div>
                                    <span class="font-semibold text-slate-700">{{ $order->guest_id }}</span>
                                @else
                                    <div class="text-lg rounded-full bg-[#2563EB]/10 flex items-center justify-center text-[#2563EB] font-black text-[10px]">
                                        {{ strtoupper(substr($order->agent->username ?? 'U', 0, 2)) }}
                                    </div>
                                    <span class="font-semibold text-slate-700">{{ $order->agent->username ?? 'N/A' }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-3 font-medium text-slate-500 text-xs sm:text-sm">{{ $order->network_type ?? 'N/A' }}</td>
                        <td class="px-4 sm:px-6 py-3 font-bold text-slate-800 text-xs sm:text-sm">GH₵{{ number_format($order->amount ?? 0, 2) }}</td>
                        <td class="px-4 sm:px-6 py-3 hidden md:table-cell">
                            <span class="px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold tracking-wide uppercase 
                                {{ $status === 'delivered' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 
                                   ($status === 'processing' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 
                                   ($status === 'failed' ? 'bg-red-50 text-red-600 border border-red-100' : 
                                    'bg-amber-50 text-amber-600 border border-amber-100')) }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 md:hidden">
                            @if($status === 'delivered')
                                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                            @elseif($status === 'processing')
                                <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                            @elseif($status === 'failed')
                                <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">No recent orders found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection