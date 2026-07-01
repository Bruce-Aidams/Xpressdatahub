�@extends('layouts.admin')

@section('page-title', 'Balance History')
@section('page-description', 'View all balance transactions')

@section('content')

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Balance History</h1>
        <p class="text-sm text-slate-400 mt-1">View all balance transactions across the platform</p>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white border border-slate-100/80 rounded-2xl p-4 shadow-sm mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by user..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50/80 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition"
                />
            </div>

            {{-- Reason Type --}}
            <select
                name="reason"
                class="px-4 py-2.5 bg-slate-50/80 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition min-w-[160px]"
            >
                <option value="">All Types</option>
                <option value="topup" {{ request('reason') === 'topup' ? 'selected' : '' }}>Top-up</option>
                <option value="order" {{ request('reason') === 'order' ? 'selected' : '' }}>Order</option>
                <option value="refund" {{ request('reason') === 'refund' ? 'selected' : '' }}>Referral</option>
                <option value="commission" {{ request('reason') === 'commission' ? 'selected' : '' }}>Commission</option>
                <option value="withdrawal" {{ request('reason') === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
            </select>

            {{-- Filter Button --}}
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-[#2563EB]/25"
            >
                <x-heroicon-o-funnel class="w-4 h-4" />
                Filter
            </button>
        </form>
    </div>

    {{-- History Table Card --}}
    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">ID</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">User</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Type</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Amount</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Balance After</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Description</th>
                        <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($history as $record)
                        <tr class="hover:bg-blue-50/20 transition">
                            <td class="px-5 py-3.5 text-slate-800 font-medium">#{{ $record->id }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-[#2563EB]/10 flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-user class="w-4 h-4 text-[#2563EB]" />
                                    </div>
                                    <span class="text-slate-700 font-medium">{{ $record->agent->username ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $reasonColors = [
                                        'topup'       => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                                        'order'       => 'bg-blue-50 text-blue-600 border border-blue-100',
                                        'refund'      => 'bg-violet-50 text-violet-600 border border-violet-100',
                                        'commission'  => 'bg-amber-50 text-amber-600 border border-amber-100',
                                        'withdrawal'  => 'bg-red-50 text-red-600 border border-red-100',
                                    ];
                                    $colorClass = $reasonColors[$record->reason] ?? 'bg-slate-50 text-slate-600 border border-slate-100';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $colorClass }}">
                                    {{ ucfirst($record->reason) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($record->change_amount >= 0)
                                    <span class="text-emerald-600 font-bold">+GH&#8373;{{ number_format(abs($record->change_amount), 2) }}</span>
                                @else
                                    <span class="text-red-500 font-bold">-GH&#8373;{{ number_format(abs($record->change_amount), 2) }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 font-medium">GH&#8373;{{ number_format($record->balance_after, 2) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="text-slate-400 text-xs leading-relaxed">{{ $record->description ?? 'N/A' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5 text-xs text-slate-400">
                                    <x-heroicon-o-clock class="w-3.5 h-3.5" />
                                    {{ $record->created_at?->format('M d, Y H:i') ?? 'N/A' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <x-heroicon-o-document-text class="w-7 h-7 text-slate-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-600">No records found</p>
                                        <p class="text-xs text-slate-400 mt-1">Try adjusting your filters or search terms</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($history->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/30">
                {{ $history->withQueryString()->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

@endsection
