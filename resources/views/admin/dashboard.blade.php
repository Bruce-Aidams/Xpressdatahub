@extends('layouts.admin')
@section('page-title', 'Dashboard')
@section('page-description', 'Real-time overview of your platform performance')
@section('content')
<!-- 4 Top Metric Cards (SaaS Style) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-6 lg:mb-8">
    <!-- Card 1: Revenue -->
    <div class="stat-card bg-moving-gradient-blue text-white rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between border-0">
        <div>
            <p class="text-[10px] sm:text-xs text-white/80 font-bold uppercase tracking-wider">Revenue</p>
            <p class="text-lg sm:text-2xl font-black text-white mt-1 sm:mt-2">GH&#8373;{{ number_format($stats['revenue'] ?? 0, 2) }}</p>
            <div class="flex items-center gap-1 mt-1.5 sm:mt-2.5">
                <span class="text-[10px] sm:text-xs font-bold text-white/90">
                    {{ $revenueChange >= 0 ? '+' : '' }}{{ $revenueChange }}%
                </span>
                <x-dynamic-component :component="$revenueChange >= 0 ? 'heroicon-o-chevron-up' : 'heroicon-o-chevron-down'" class="text-white/90 w-3 h-3" />
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
            <x-heroicon-o-currency-dollar class="w-5 h-5 text-white" />
        </div>
    </div>

    <!-- Card 2: Orders -->
    <div class="stat-card bg-moving-gradient-blue text-white rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between border-0">
        <div>
            <p class="text-[10px] sm:text-xs text-white/80 font-bold uppercase tracking-wider">Orders</p>
            <p class="text-lg sm:text-2xl font-black text-white mt-1 sm:mt-2">{{ number_format($stats['total_orders'] ?? 0) }}</p>
            <div class="flex items-center gap-1 mt-1.5 sm:mt-2.5">
                <span class="text-[10px] sm:text-xs font-bold text-white/90">
                    {{ $orderChange >= 0 ? '+' : '' }}{{ $orderChange }}%
                </span>
                <x-dynamic-component :component="$orderChange >= 0 ? 'heroicon-o-chevron-up' : 'heroicon-o-chevron-down'" class="text-white/90 w-3 h-3" />
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
            <x-heroicon-o-shopping-bag class="w-5 h-5 text-white" />
        </div>
    </div>

    <!-- Card 3: Total Agents -->
    <div class="stat-card bg-moving-gradient-blue text-white rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between border-0">
        <div>
            <p class="text-[10px] sm:text-xs text-white/80 font-bold uppercase tracking-wider">Agents</p>
            <p class="text-lg sm:text-2xl font-black text-white mt-1 sm:mt-2">{{ number_format($stats['total_agents'] ?? 0) }}</p>
            <div class="flex items-center gap-1 mt-1.5 sm:mt-2.5">
                <span class="text-[10px] sm:text-xs font-bold text-white/90">
                    {{ $agentChange >= 0 ? '+' : '' }}{{ $agentChange }}%
                </span>
                <x-dynamic-component :component="$agentChange >= 0 ? 'heroicon-o-chevron-up' : 'heroicon-o-chevron-down'" class="text-white/90 w-3 h-3" />
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
            <x-heroicon-o-users class="w-5 h-5 text-white" />
        </div>
    </div>

    <!-- Card 4: Active Shops -->
    <div class="stat-card bg-moving-gradient-blue text-white rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between border-0">
        <div>
            <p class="text-[10px] sm:text-xs text-white/80 font-bold uppercase tracking-wider">Shops</p>
            <p class="text-lg sm:text-2xl font-black text-white mt-1 sm:mt-2">{{ number_format($stats['active_shops'] ?? 0) }}</p>
            <div class="flex items-center gap-1 mt-1.5 sm:mt-2.5">
                <span class="text-[10px] sm:text-xs font-bold text-white/90">{{ $shopChange }}% active</span>
                <x-heroicon-o-chevron-up class="w-3 h-3 text-white/90" />
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
            <x-heroicon-o-building-storefront class="w-5 h-5 text-white" />
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
            <a href="{{ route('admin.analytics.index') }}" class="text-[10px] sm:text-xs font-semibold text-slate-600 hover:text-slate-800 transition flex items-center gap-1">
                Report <x-heroicon-o-chevron-right class="w-5 h-5" />
            </a>
        </div>
        <!-- SVG Bar Chart -->
        <div class="h-44 sm:h-64 relative px-1 sm:px-2 pb-2">
            {{-- Grid lines --}}
            <div class="absolute inset-0 flex flex-col justify-between px-1 sm:px-2 pb-6 pointer-events-none">
                @for($i = 0; $i < 4; $i++)
                    <div class="border-b border-dashed border-slate-100/80 w-full"></div>
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
                        <span class="text-[8px] sm:text-[10px] font-bold {{ $isToday ? 'text-blue-700' : 'text-slate-500' }}">{{ $dayData['count'] }}</span>
                        @endif
                        <div class="w-full rounded-t-xl transition-all duration-500"
                             style="height: {{ max($height, 3) }}%; background: {{ $isToday ? 'linear-gradient(180deg, #2563eb, #4f46e5)' : 'linear-gradient(180deg, #bfdbfe, #dbeafe)' }};
                                    {{ $isToday ? 'box-shadow: 0 4px 14px -3px rgba(37,99,235,0.4);' : '' }}"
                        ></div>
                        <span class="text-[8px] sm:text-[10px] {{ $isToday ? 'font-bold text-blue-700' : 'text-slate-400 font-medium' }}">{{ $dayData['day'] }}</span>
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
            <x-heroicon-o-information-circle class="w-5 h-5 text-slate-400" />
        </div>
        @php
            $activePercentage = $totalShops > 0 ? round(($activeShops / $totalShops) * 100) : 0;
            $dashOffset = 251.2 - (251.2 * $activePercentage / 100);
        @endphp
        <div class="relative w-32 h-32 sm:w-40 sm:h-40 mx-auto flex items-center justify-center">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                <defs>
                    <linearGradient id="gaugeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#2563eb" />
                        <stop offset="50%" stop-color="#7c3aed" />
                        <stop offset="100%" stop-color="#0d9488" />
                    </linearGradient>
                </defs>
                <circle cx="50" cy="50" r="40" stroke="#e2e8f0" stroke-width="8" fill="transparent"></circle>
                <circle cx="50" cy="50" r="40" stroke="url(#gaugeGrad)" stroke-width="8" fill="transparent" 
                        stroke-dasharray="251.2" stroke-dashoffset="{{ $dashOffset }}" stroke-linecap="round"
                        style="filter: drop-shadow(0 2px 6px rgba(37,99,235,0.3));"></circle>
            </svg>
            <div class="absolute text-center">
                <span class="text-2xl sm:text-3xl font-black bg-gradient-to-r from-blue-600 via-violet-600 to-teal-500 bg-clip-text text-transparent">{{ $activePercentage }}%</span>
                <p class="text-[8px] sm:text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Active</p>
            </div>
        </div>
        <div class="flex justify-between border-t border-slate-100 pt-3 sm:pt-4 mt-3 sm:mt-4">
            <div class="text-center flex-1 border-r border-slate-100">
                <span class="text-[10px] sm:text-xs text-slate-400">Total Shops</span>
                <p class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5">{{ number_format($totalShops) }}</p>
            </div>
            <div class="text-center flex-1">
                <span class="text-[10px] sm:text-xs text-slate-400">Active</span>
                <p class="text-xs sm:text-sm font-bold text-emerald-600 mt-0.5">{{ number_format($activeShops) }}</p>
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
            $networkColors = ['MTN' => '#f59e0b', 'Telecel' => '#ef4444', 'AirtelTigo' => '#3b82f6'];
            $networkGradients = [
                'MTN'        => ['#f59e0b', '#f97316'],
                'Telecel'    => ['#ef4444', '#ec4899'],
                'AirtelTigo' => ['#3b82f6', '#6366f1'],
            ];
            $networkLabels = ['MTN' => 'MTN', 'Telecel' => 'Telecel', 'AirtelTigo' => 'AirtelTigo'];
            $circumference = 238.7;
            $cumulativeOffset = 0;
            $gradIdx = 0;
        @endphp
        <div class="relative w-32 h-32 sm:w-36 sm:h-36 mx-auto flex items-center justify-center my-2">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                <defs>
                    @forelse($networkStats as $net)
                        @php
                            $gColors = $networkGradients[$net->network_type] ?? ['#94A3B8', '#cbd5e1'];
                        @endphp
                        <linearGradient id="donutGrad{{ $gradIdx }}" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="{{ $gColors[0] }}" />
                            <stop offset="100%" stop-color="{{ $gColors[1] }}" />
                        </linearGradient>
                        @php $gradIdx++; @endphp
                    @empty
                    @endforelse
                </defs>
                @php $gradIdx = 0; @endphp
                @forelse($networkStats as $net)
                    @php
                        $pct = $totalNetworkOrders > 0 ? ($net->total / $totalNetworkOrders) : 0;
                        $dashLen = $circumference * $pct;
                        $rotation = ($cumulativeOffset / $circumference) * 360;
                    @endphp
                    <circle cx="50" cy="50" r="38" stroke="url(#donutGrad{{ $gradIdx }})" stroke-width="10" fill="transparent"
                            stroke-dasharray="{{ $dashLen }} {{ $circumference - $dashLen }}"
                            transform="rotate({{ $rotation }} 50 50)"
                            style="filter: drop-shadow(0 1px 3px rgba(0,0,0,0.1));"></circle>
                    @php $cumulativeOffset += $dashLen; $gradIdx++; @endphp
                @empty
                    <circle cx="50" cy="50" r="38" stroke="#e2e8f0" stroke-width="10" fill="transparent"></circle>
                @endforelse
            </svg>
            <div class="absolute text-center">
                <span class="text-xl sm:text-2xl font-black text-slate-800">{{ number_format($totalNetworkOrders) }}</span>
                <p class="text-[8px] sm:text-[9px] text-slate-400 font-bold uppercase">Orders</p>
            </div>
        </div>
        <div class="space-y-2.5 mt-3 sm:mt-4 text-[10px] sm:text-xs font-semibold text-slate-600">
            @forelse($networkStats as $net)
                @php
                    $pct = $totalNetworkOrders > 0 ? round(($net->total / $totalNetworkOrders) * 100) : 0;
                    $color = $networkColors[$net->network_type] ?? '#94A3B8';
                @endphp
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full shadow-sm" style="background: {{ $color }}"></span>
                            {{ $net->network_type }}
                        </span>
                        <span class="font-bold">{{ $pct }}%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700" style="width: {{ $pct }}%; background: {{ $color }};"></div>
                    </div>
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
            <a href="{{ route('admin.agents.index') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 bg-slate-50 hover:bg-slate-100 border border-slate-100 rounded-xl transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-600 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-users class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-600">Agents</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 bg-slate-50 hover:bg-slate-100 border border-slate-100 rounded-xl transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-600 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-shopping-cart class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-600">Orders</span>
            </a>
            <a href="{{ route('admin.pricing.index') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 bg-slate-50 hover:bg-slate-100 border border-slate-100 rounded-xl transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-600 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-tag class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-600">Pricing</span>
            </a>
            <a href="{{ route('admin.analytics.index') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 bg-slate-50 hover:bg-slate-100 border border-slate-100 rounded-xl transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-600 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-chart-bar class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-600">Analytics</span>
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
            <x-heroicon-o-star class="w-5 h-5 text-amber-400" />
        </div>
        <div class="space-y-3 sm:space-y-4 flex-1 justify-center flex flex-col">
            @forelse($topPackages as $pkg)
                <div class="flex items-center justify-between {{ !$loop->last ? 'border-b border-slate-100 pb-2' : '' }}">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span class="text-lg sm:w-7 sm:h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-[10px] sm:text-xs font-black">{{ $loop->iteration }}</span>
                        <div>
                            <p class="text-[10px] sm:text-xs font-bold text-slate-800">{{ $pkg->network_type }} {{ $pkg->package_size }}</p>
                            <p class="text-[9px] sm:text-[10px] text-slate-400">Data Bundle</p>
                        </div>
                    </div>
                    <span class="text-[10px] sm:text-xs font-black text-slate-800 whitespace-nowrap">{{ number_format($pkg->total) }} Sold</span>
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
                    @php
                        /** @var \App\Models\Agent|\stdClass $u */
                        $st = data_get($u, 'status', 'active');
                        $firstName = data_get($u, 'first_name', '');
                        $lastName = data_get($u, 'last_name', '');
                        $username = data_get($u, 'username', 'U');
                        $email = data_get($u, 'email', 'N/A');
                        $phone = data_get($u, 'phone', 'N/A');
                        $role = data_get($u, 'role', 'agent');
                        $createdAt = data_get($u, 'created_at');
                        $joined = is_object($createdAt) && method_exists($createdAt, 'diffForHumans') ? $createdAt->diffForHumans() : 'N/A';
                    @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition">
                        <td class="px-4 sm:px-6 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#2563EB] to-[#60A5FA] flex items-center justify-center text-white font-bold text-[10px]">
                                    {{ strtoupper(substr($firstName ?: $username, 0, 1)) }}{{ strtoupper(substr($lastName, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-800 text-xs sm:text-sm">{{ $firstName }} {{ $lastName }}</span>
                                    <p class="text-[10px] text-slate-400">&#64;{{ $username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-3 hidden sm:table-cell text-slate-500 text-xs">{{ $email }}</td>
                        <td class="px-4 sm:px-6 py-3 hidden md:table-cell text-slate-500 text-xs">{{ $phone }}</td>
                        <td class="px-4 sm:px-6 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold uppercase tracking-wide bg-slate-100 text-slate-600">{{ ucfirst($role) }}</span>
                        </td>
                        <td class="px-4 sm:px-6 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold uppercase tracking-wide
                                {{ $st === 'active' ? 'bg-emerald-50 text-emerald-600' : ($st === 'suspended' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600') }}">
                                {{ ucfirst($st) }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 hidden lg:table-cell text-slate-400 text-[10px] sm:text-xs">{{ $joined }}</td>
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
                    @php
                        /** @var \App\Models\Order|\stdClass $order */
                        $orderId = data_get($order, 'id');
                        $status = data_get($order, 'status', 'pending');
                        $guestId = data_get($order, 'guest_id');
                        $agentUsername = data_get($order, 'agent.username', 'N/A');
                        $networkType = data_get($order, 'network_type', 'N/A');
                        $amount = data_get($order, 'amount', 0);
                    @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition">
                        <td class="px-4 sm:px-6 py-3 font-black text-slate-800 text-xs sm:text-sm">#{{ $orderId }}</td>
                        <td class="px-4 sm:px-6 py-3 hidden sm:table-cell">
                            <div class="flex items-center gap-2.5">
                                @if($guestId)
                                    <div class="text-lg rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-black text-[10px]">
                                        {{ strtoupper(substr($guestId, 0, 2)) }}
                                    </div>
                                    <span class="font-semibold text-slate-700">{{ $guestId }}</span>
                                @else
                                    <div class="text-lg rounded-full bg-[#2563EB]/10 flex items-center justify-center text-[#2563EB] font-black text-[10px]">
                                        {{ strtoupper(substr($agentUsername, 0, 2)) }}
                                    </div>
                                    <span class="font-semibold text-slate-700">{{ $agentUsername }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-3 font-medium text-slate-500 text-xs sm:text-sm">{{ $networkType }}</td>
                        <td class="px-4 sm:px-6 py-3 font-bold text-slate-800 text-xs sm:text-sm">GH&#8373;{{ number_format($amount, 2) }}</td>
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