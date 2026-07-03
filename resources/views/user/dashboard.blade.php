@extends('layouts.user')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description')
Welcome back, {{ $currentUser->username ?? '' }}
@endsection
@section('content')

@if($activeBanner)
@php
    $bannerData = is_array($activeBanner['data']) ? $activeBanner['data'] : (json_decode($activeBanner['data'], true) ?? []);
    $bgColor = $bannerData['background_color'] ?? '#1e40af';
    $textColor = $bannerData['text_color'] ?? '#ffffff';
    $speed = $bannerData['speed'] ?? 50;
@endphp
<div id="bannerPopup" class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display:none;">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm banner-backdrop" onclick="closeBanner()"></div>
    <div class="relative w-full max-w-md animate-banner-in">
        <div class="rounded-3xl overflow-hidden shadow-2xl" style="background: {{ $bgColor }};">
            {{-- Decorative shapes --}}
            <div class="relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full opacity-10" style="background: {{ $textColor }};"></div>
                <div class="absolute -bottom-8 -left-8 w-24 h-24 rounded-full opacity-10" style="background: {{ $textColor }};"></div>
                <div class="absolute top-1/2 right-8 w-16 h-16 rounded-full opacity-5" style="background: {{ $textColor }};"></div>

                <div class="relative px-6 pt-8 pb-6 sm:px-8 sm:pt-10 sm:pb-8">
                    {{-- Icon --}}
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center mb-4 sm:mb-5" style="background: {{ $textColor }}20;">
                        <x-heroicon-o-megaphone class="w-6 h-6 sm:w-7 sm:h-7" style="color: {{ $textColor }};" />
                    </div>

                    {{-- Title --}}
                    <h3 class="text-lg sm:text-xl font-black mb-2" style="color: {{ $textColor }};">
                        {{ $activeBanner['title'] ?? 'Announcement' }}
                    </h3>

                    {{-- Message --}}
                    <p class="text-sm sm:text-base leading-relaxed opacity-90" style="color: {{ $textColor }};">
                        {{ $activeBanner['message'] }}
                    </p>

                    {{-- Divider --}}
                    <div class="my-5 sm:my-6 h-px opacity-20" style="background: {{ $textColor }};"></div>

                    {{-- Close button --}}
                    <button onclick="closeBanner()" class="w-full py-2.5 sm:py-3 rounded-xl text-sm font-bold transition-all duration-200 hover:opacity-80" style="background: {{ $textColor }}; color: {{ $bgColor }};">
                        Got it
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- 4 Top Metric Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-5 sm:mb-6 lg:mb-8">
    <!-- Wallet Balance -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Balance</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1 sm:mt-2">GH&#8373;{{ number_format($agent->balance ?? 0, 2) }}</p>
            <div class="flex items-center gap-1 mt-1 sm:mt-2">
                <span class="text-[10px] sm:text-xs font-bold {{ $spendChange >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                    {{ $spendChange >= 0 ? '+' : '' }}{{ $spendChange }}% this week
                </span>
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-[#EA580C]/10 flex items-center justify-center">
            <x-heroicon-o-wallet class="text-[#EA580C] w-5 h-5 sm:w-6 sm:h-6" />
        </div>
    </div>

    <!-- Today's Orders -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Today</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1 sm:mt-2">{{ $todayOrders }}</p>
            <div class="flex items-center gap-1 mt-1 sm:mt-2">
                <span class="text-[10px] sm:text-xs font-bold text-slate-400">GH&#8373;{{ number_format($todaySpent, 2) }} spent</span>
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-blue-50 flex items-center justify-center">
            <x-heroicon-o-shopping-bag class="text-blue-500 w-5 h-5 sm:w-6 sm:h-6" />
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">All Orders</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1 sm:mt-2">{{ number_format($totalOrders) }}</p>
            <div class="flex items-center gap-1 mt-1 sm:mt-2">
                <span class="text-[10px] sm:text-xs font-bold {{ $orderChange >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                    {{ $orderChange >= 0 ? '+' : '' }}{{ $orderChange }}% this week
                </span>
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 flex items-center justify-center">
            <x-heroicon-o-chart-bar class="text-emerald-500 w-5 h-5 sm:w-6 sm:h-6" />
        </div>
    </div>

    <!-- Referrals -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Referrals</p>
            <p class="text-lg sm:text-2xl font-black text-slate-800 mt-1 sm:mt-2">{{ $referralCount }}</p>
            <div class="flex items-center gap-1 mt-1 sm:mt-2">
                <span class="text-[10px] sm:text-xs font-bold text-[#EA580C]">GH&#8373;{{ number_format($referralEarnings, 2) }} earned</span>
            </div>
        </div>
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-purple-50 flex items-center justify-center">
            <x-heroicon-o-user-group class="text-purple-500 w-5 h-5 sm:w-6 sm:h-6" />
        </div>
    </div>
</div>

<!-- Row 2: Bar Chart & Order Status -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-5 sm:mb-6 lg:mb-8">
    <!-- Bar Chart Card -->
    <div class="lg:col-span-2 bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Weekly Activity</h3>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Orders per day</p>
            </div>
            <a href="{{ route('user.orders.today') }}" class="text-[10px] sm:text-xs font-semibold text-[#EA580C] hover:text-[#C2410C] transition flex items-center gap-1">
                View All <x-heroicon-o-chevron-right class="w-3 h-3" />
            </a>
        </div>
        <div class="h-44 sm:h-64 flex items-end justify-between gap-1.5 sm:gap-2 px-1 sm:px-2 border-b border-slate-100 pb-2">
            @foreach($weeklyOrders as $i => $dayData)
                @php
                    $height = $maxWeekly > 0 ? ($dayData['count'] / $maxWeekly) * 220 : 0;
                    $isToday = $loop->last;
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1 sm:gap-2">
                    <div class="w-full {{ $isToday ? 'bg-[#EA580C] shadow-lg shadow-orange-500/10' : 'bg-slate-100 hover:bg-[#EA580C]/20' }} rounded-t-lg transition-all duration-500" style="height: {{ max($height, 4) }}px;"></div>
                    <span class="text-[8px] sm:text-[10px] {{ $isToday ? 'font-bold text-[#EA580C]' : 'text-slate-400 font-medium' }}">{{ $dayData['day'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Order Status Card -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Order Status</h3>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">All time breakdown</p>
            </div>
        </div>
        @php
            $completed = $ordersByStatus->whereIn('status', ['completed', 'delivered'])->sum('total');
            $pending = $ordersByStatus->where('status', 'pending')->first()->total ?? 0;
            $failed = $ordersByStatus->where('status', 'failed')->first()->total ?? 0;
            $processing = $ordersByStatus->where('status', 'processing')->first()->total ?? 0;
            $totalAll = max($completed + $pending + $failed + $processing, 1);
            $completedPct = round(($completed / $totalAll) * 100);
            $dashOffset = 251.2 - (251.2 * $completedPct / 100);
        @endphp
        <div class="relative w-32 h-32 sm:w-40 sm:h-40 mx-auto flex items-center justify-center">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="40" stroke="#f1f5f9" stroke-width="8" fill="transparent"></circle>
                <circle cx="50" cy="50" r="40" stroke="#10B981" stroke-width="8" fill="transparent"
                        stroke-dasharray="251.2" stroke-dashoffset="{{ $dashOffset }}" stroke-linecap="round"></circle>
            </svg>
            <div class="absolute text-center">
                <span class="text-2xl sm:text-3xl font-black text-slate-800">{{ $completedPct }}%</span>
                <p class="text-[8px] sm:text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Success</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 mt-3 sm:mt-4 text-[10px] sm:text-xs">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-slate-500">Completed</span>
                <span class="font-bold text-slate-800 ml-auto">{{ $completed }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span class="text-slate-500">Pending</span>
                <span class="font-bold text-slate-800 ml-auto">{{ $pending }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                <span class="text-slate-500">Failed</span>
                <span class="font-bold text-slate-800 ml-auto">{{ $failed }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span class="text-slate-500">Processing</span>
                <span class="font-bold text-slate-800 ml-auto">{{ $processing }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Network Breakdown, Top Packages, Quick Actions -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-5 sm:mb-6 lg:mb-8">
    <!-- Network Breakdown -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">By Network</h3>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Order distribution</p>
            </div>
        </div>
        @php
            $networkColors = ['MTN' => '#F59E0B', 'Telecel' => '#EF4444', 'AirtelTigo' => '#3B82F6'];
            $offset = 0;
            $circumference = 238.7;
        @endphp
        <div class="relative w-32 h-32 sm:w-36 sm:h-36 mx-auto flex items-center justify-center my-2">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                @forelse($networkStats as $net)
                    @php
                        $pct = $totalNetworkOrders > 0 ? ($net->total / $totalNetworkOrders) : 0;
                        $dashLen = $circumference * $pct;
                        $color = $networkColors[$net->network_type] ?? '#94A3B8';
                    @endphp
                    <circle cx="50" cy="50" r="38" stroke="{{ $color }}" stroke-width="10" fill="transparent"
                            stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"></circle>
                    @php $offset += $dashLen; @endphp
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
                <div class="text-center text-slate-400 py-2">No orders yet</div>
            @endforelse
        </div>
    </div>

    <!-- Top Packages -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Top Packages</h3>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Most ordered bundles</p>
            </div>
            <x-heroicon-o-star class="text-[#EA580C] w-5 h-5" />
        </div>
        <div class="space-y-3 sm:space-y-4 flex-1 justify-center flex flex-col">
            @forelse($topPackages as $pkg)
                <div class="flex items-center justify-between {{ !$loop->last ? 'border-b border-slate-50 pb-2' : '' }}">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span class="text-lg sm:w-7 sm:h-7 rounded-lg {{ $loop->iteration === 1 ? 'bg-amber-100 text-amber-700' : ($loop->iteration === 2 ? 'bg-slate-100 text-slate-700' : 'bg-orange-100 text-orange-700') }} flex items-center justify-center text-[10px] sm:text-xs font-black">{{ $loop->iteration }}</span>
                        <div>
                            <p class="text-[10px] sm:text-xs font-bold text-slate-800"><x-network-badge :network="$pkg->network_type" /> {{ $pkg->package_size }}</p>
                            <p class="text-[9px] sm:text-[10px] text-slate-400">Data Bundle</p>
                        </div>
                    </div>
                    <span class="text-[10px] sm:text-xs font-black text-slate-700 whitespace-nowrap">{{ number_format($pkg->total) }}x</span>
                </div>
            @empty
                <div class="text-center text-slate-400 py-4 text-xs">No package data yet</div>
            @endforelse
        </div>
    </div>

    <!-- Quick Actions & Top Up -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Quick Actions</h3>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Common operations</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2.5 sm:gap-3 flex-1 items-center">
            <a href="{{ route('user.orders.today') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-list-bullet class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-700">Orders</span>
            </a>
            <a href="{{ route('user.shop.index') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-cyan-50 flex items-center justify-center text-cyan-500 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-building-storefront class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-700">My Shop</span>
            </a>
            <a href="{{ route('user.referrals.index') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-500 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-user-group class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-700">Referrals</span>
            </a>
            <a href="{{ route('user.balance-history.index') }}" class="flex flex-col items-center justify-center p-3 sm:p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition text-center group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 mb-1.5 sm:mb-2 group-hover:scale-110 transition"><x-heroicon-o-chart-bar class="w-5 h-5" /></div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-700">History</span>
            </a>
        </div>
        <div class="mt-3 sm:mt-4">
            <a href="{{ route('user.wallet.topup') }}" class="w-full py-2.5 sm:py-3 bg-[#EA580C] hover:bg-[#C2410C] text-white text-[10px] sm:text-xs font-black rounded-xl shadow-md shadow-orange-500/10 transition-all flex items-center justify-center gap-2">
                <x-heroicon-o-plus-circle class="w-5 h-5" /> TOP UP BALANCE
            </a>
        </div>
    </div>
</div>

<!-- Row 4: Latest Orders -->
<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Latest Orders</h3>
            <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Most recent activities</p>
        </div>
        <a href="{{ route('user.orders.today') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 text-[10px] sm:text-xs font-bold rounded-xl transition flex items-center gap-1.5 sm:gap-2">
            View All <x-heroicon-o-arrow-top-right-on-square class="w-3 h-3" />
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50">
                    <th class="px-3 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">ID</th>
                    <th class="px-3 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider hidden sm:table-cell">Phone</th>
                    <th class="px-3 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Network</th>
                    <th class="px-3 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Amount</th>
                    <th class="px-3 sm:px-6 py-3 text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider hidden md:table-cell">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr class="border-b border-slate-100 hover:bg-orange-50/20 transition">
                        <td class="px-3 sm:px-6 py-3 sm:py-4">
                            <div class="font-black text-slate-800 text-xs sm:text-sm">#{{ $order->id }}</div>
                            <div class="text-[9px] text-slate-400 sm:hidden">{{ $order->phone_number }}</div>
                            <div class="flex items-center gap-1 sm:hidden mt-0.5">
                                @if(in_array($order->status, ['delivered', 'completed']))
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                @elseif($order->status === 'failed')
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 hidden sm:table-cell">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-xs"><x-heroicon-o-phone class="w-3.5 h-3.5" /></div>
                                <span class="font-semibold text-slate-700 text-xs">{{ $order->phone_number }}</span>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm"><x-network-badge :network="$order->network_type" /></td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 font-bold text-slate-800 text-xs sm:text-sm">GH&#8373;{{ number_format($order->amount, 2) }}</td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 hidden md:table-cell">
                            <x-status-badge :status="$order->status" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium text-xs sm:text-sm">No orders yet. Place your first order!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes bannerIn {
        0% { opacity: 0; transform: scale(0.9) translateY(20px); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    @keyframes bannerOut {
        0% { opacity: 1; transform: scale(1) translateY(0); }
        100% { opacity: 0; transform: scale(0.9) translateY(20px); }
    }
    @keyframes backdropFadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }
    @keyframes backdropFadeOut {
        0% { opacity: 1; }
        100% { opacity: 0; }
    }
    .animate-banner-in { animation: bannerIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .animate-banner-out { animation: bannerOut 0.3s cubic-bezier(0.55, 0, 1, 0.45) forwards; }
    .banner-backdrop { animation: backdropFadeIn 0.3s ease forwards; }
    .banner-backdrop-out { animation: backdropFadeOut 0.3s ease forwards; }
</style>
@endpush

@push('scripts')
<script>
(function() {
    var banner = document.getElementById('bannerPopup');
    if (!banner) return;

    var dismissed = sessionStorage.getItem('banner_dismissed_{{ $activeBanner["id"] ?? 0 }}');
    if (dismissed) {
        banner.style.display = 'none';
        return;
    }

    banner.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    window.closeBanner = function() {
        var popup = document.getElementById('bannerPopup');
        if (!popup) return;
        var inner = popup.querySelector('.animate-banner-in');
        var bd = popup.querySelector('.banner-backdrop');
        if (inner) { inner.classList.remove('animate-banner-in'); inner.classList.add('animate-banner-out'); }
        if (bd) { bd.classList.remove('banner-backdrop'); bd.classList.add('banner-backdrop-out'); }
        setTimeout(function() {
            popup.style.display = 'none';
            document.body.style.overflow = '';
            sessionStorage.setItem('banner_dismissed_{{ $activeBanner["id"] ?? 0 }}', '1');
        }, 300);
    };
})();
</script>
@endpush
