�@extends('layouts.user')
@section('title', 'Balance History')
@section('page-title', 'Balance History')
@section('page-description', 'View your balance changes')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-black text-slate-800">Balance History</h1>
    <p class="text-sm text-slate-400 mt-1">View your balance changes</p>
</div>

<div class="bg-white border border-slate-100/80 rounded-2xl p-4 shadow-sm mb-6">
    <form method="GET" class="flex items-center gap-3">
        <select name="type" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/30 outline-none transition">
            <option value="">All Types</option>
            <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
            <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
        </select>
        <button type="submit" class="px-5 py-2.5 bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-semibold rounded-xl transition flex items-center gap-2">
            <x-heroicon-o-funnel class="w-4 h-4" />
            Filter
        </button>
    </form>
</div>

<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Type</th>
                    <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Amount</th>
                    <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Balance After</th>
                    <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Description</th>
                    <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $record)
                    @php
                        $isCredit = ($record->change_amount ?? 0) >= 0;
                    @endphp
                    <tr class="border-b border-slate-100/60 last:border-0 hover:bg-orange-50/20 transition">
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isCredit ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
                                {{ $isCredit ? 'Credit' : 'Debit' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 {{ $isCredit ? 'text-emerald-600' : 'text-red-500' }} font-bold">
                            {{ $isCredit ? '+' : '-' }}GH₵{{ number_format(abs($record->change_amount ?? 0), 2) }}
                        </td>
                        <td class="px-5 py-4 text-slate-600">GH₵{{ number_format($record->balance_after ?? 0, 2) }}</td>
                        <td class="px-5 py-4 text-slate-400 text-xs">{{ ucfirst($record->reason ?? 'N/A') }}</td>
                        <td class="px-5 py-4 text-slate-400 text-xs">{{ $record->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                                    <x-heroicon-o-document-text class="w-6 h-6 text-slate-400" />
                                </div>
                                <p class="text-sm text-slate-500 font-medium">No balance records found</p>
                                <p class="text-xs text-slate-400">Your balance history will appear here</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($history->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $history->withQueryString()->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
