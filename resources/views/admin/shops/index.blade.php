�@extends('layouts.admin')
@section('page-title', 'Shop Management')
@section('page-description', 'Manage vendor shops, orders, and withdrawals')

@php
    $totalShops = $shops->total();
    $activeCount = $shops->getCollection()->where('is_active', true)->count();
    $inactiveCount = $shops->getCollection()->where('is_active', false)->count();
@endphp

@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <div class="flex items-center gap-3 mb-1">
        <div class="w-10 h-10 rounded-xl bg-[#2563EB]/10 flex items-center justify-center">
            <x-heroicon-s-building-storefront class="w-5 h-5 text-[#2563EB]" />
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-800">Shop Management</h1>
            <p class="text-sm text-slate-400 mt-0.5">Manage vendor shops, orders, and withdrawals</p>
        </div>
    </div>
</div>

{{-- Stats Row --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="stat-card bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Total Shops</p>
                <p class="text-2xl font-black text-slate-800">{{ $totalShops }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                <x-heroicon-o-building-storefront class="w-5 h-5 text-blue-500" />
            </div>
        </div>
    </div>
    <div class="stat-card bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Active</p>
                <p class="text-2xl font-black text-emerald-600">{{ $activeCount }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center">
                <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-500" />
            </div>
        </div>
    </div>
    <div class="stat-card bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Inactive</p>
                <p class="text-2xl font-black text-red-500">{{ $inactiveCount }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center">
                <x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />
            </div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="bg-white border border-slate-100/80 rounded-2xl p-4 shadow-sm mb-6">
    <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <div class="relative flex-1">
            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search shops by name or slug..."
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 transition"
            />
        </div>
        <div class="relative">
            <x-heroicon-o-funnel class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            <select
                name="status"
                class="appearance-none pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 transition cursor-pointer min-w-[160px]"
            >
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <x-heroicon-o-chevron-down class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
        </div>
        <button
            type="submit"
            class="px-5 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl transition flex items-center justify-center gap-2"
        >
            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
            Filter
        </button>
        @if(request('search') || request('status'))
            <a
                href="{{ route('admin.shops.index') }}"
                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold rounded-xl transition flex items-center justify-center gap-1.5"
            >
                <x-heroicon-o-x-mark class="w-4 h-4" />
                Clear
            </a>
        @endif
    </form>
</div>

{{-- Shops Table --}}
<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-slate-50/60 border-b border-slate-100">
                    <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Shop</th>
                    <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Owner</th>
                    <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                    <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Revenue</th>
                    <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($shops as $shop)
                    <tr class="hover:bg-blue-50/20 transition">
                        {{-- Shop --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#2563EB]/10 flex items-center justify-center text-[#2563EB] text-sm font-bold shrink-0">
                                    {{ strtoupper(substr($shop->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-xs">{{ $shop->name }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $shop->slug ?? 'slug' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Owner --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-[10px] font-bold shrink-0">
                                    {{ strtoupper(substr($shop->agent->username ?? 'NA', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-700">{{ $shop->agent->username ?? 'N/A' }}</p>
                                    <p class="text-[10px] text-slate-400">ID: {{ $shop->agent->id ?? '�' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            @if($shop->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold text-emerald-600 bg-emerald-50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold text-red-600 bg-red-50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>

                        {{-- Revenue --}}
                        <td class="px-6 py-4 text-right">
                            <span class="text-xs font-bold text-slate-700">GH&#8373;{{ number_format($shop->total_revenue ?? 0, 2) }}</span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.shops.show', $shop->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-[#2563EB] bg-[#2563EB]/10 hover:bg-[#2563EB]/20 rounded-lg transition"
                                   title="View Shop">
                                    <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                    View
                                </a>
                                <form method="POST" action="{{ route('admin.shops.status', $shop->id) }}" class="inline-block">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="is_active" value="{{ $shop->is_active ? '0' : '1' }}">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold rounded-lg transition {{ $shop->is_active ? 'text-red-600 bg-red-50 hover:bg-red-100' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100' }}"
                                        title="{{ $shop->is_active ? 'Deactivate' : 'Activate' }} Shop"
                                    >
                                        @if($shop->is_active)
                                            <x-heroicon-o-x-circle class="w-3.5 h-3.5" />
                                            Deactivate
                                        @else
                                            <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                            Activate
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                                    <x-heroicon-o-building-storefront class="w-7 h-7 text-slate-300" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-500">No shops found</p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        @if(request('search') || request('status'))
                                            Try adjusting your filters or <a href="{{ route('admin.shops.index') }}" class="text-[#2563EB] hover:underline font-medium">clear the search</a>.
                                        @else
                                            No vendor shops have been created yet.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($shops->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-400 font-medium">
                Showing {{ $shops->firstItem() ?? 0 }} to {{ $shops->lastItem() ?? 0 }} of {{ $shops->total() }} shops
            </p>
            <div>
                {{ $shops->withQueryString()->links('pagination::tailwind') }}
            </div>
        </div>
    @endif
</div>

{{-- Quick Links --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
    <a href="{{ route('admin.shop-orders.index') }}" class="stat-card group bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm flex items-center gap-4 hover:border-[#2563EB]/30">
        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-[#2563EB]/10 transition">
            <x-heroicon-o-shopping-cart class="w-6 h-6 text-blue-500 group-hover:text-[#2563EB] transition" />
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-slate-800">Shop Orders</p>
            <p class="text-[11px] text-slate-400 mt-0.5 truncate">View and manage all vendor orders</p>
        </div>
        <x-heroicon-o-chevron-right class="w-5 h-5 text-slate-300 group-hover:text-[#2563EB] transition shrink-0" />
    </a>

    <a href="{{ route('admin.shop-withdrawals.index') }}" class="stat-card group bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm flex items-center gap-4 hover:border-[#2563EB]/30">
        <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center group-hover:bg-[#2563EB]/10 transition">
            <x-heroicon-o-banknotes class="w-6 h-6 text-amber-500 group-hover:text-[#2563EB] transition" />
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-slate-800">Shop Withdrawals</p>
            <p class="text-[11px] text-slate-400 mt-0.5 truncate">Review and process withdrawal requests</p>
        </div>
        <x-heroicon-o-chevron-right class="w-5 h-5 text-slate-300 group-hover:text-[#2563EB] transition shrink-0" />
    </a>

    <a href="{{ route('admin.shops.index') }}" class="stat-card group bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm flex items-center gap-4 hover:border-[#2563EB]/30">
        <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center group-hover:bg-[#2563EB]/10 transition">
            <x-heroicon-o-chart-bar class="w-6 h-6 text-purple-500 group-hover:text-[#2563EB] transition" />
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-slate-800">Shop Analytics</p>
            <p class="text-[11px] text-slate-400 mt-0.5 truncate">Performance metrics and insights</p>
        </div>
        <x-heroicon-o-chevron-right class="w-5 h-5 text-slate-300 group-hover:text-[#2563EB] transition shrink-0" />
    </a>
</div>

@endsection
