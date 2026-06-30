�@extends('layouts.user')
@section('title', 'Shop Profits')
@section('page-title', 'Shop Profits')
@section('page-description', 'Track your shop earnings')
@section('content')
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-5">
        <p class="text-xs text-slate-400">Total Profit</p>
        <p class="text-2xl font-bold text-amber-400 mt-1">GH₵{{ number_format($totalProfit ?? 0, 2) }}</p>
    </div>
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-5">
        <p class="text-xs text-slate-400">Withdrawn</p>
        <p class="text-2xl font-bold text-cyan-400 mt-1">GH₵{{ number_format($totalWithdrawn ?? 0, 2) }}</p>
    </div>
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-5">
        <p class="text-xs text-slate-400">Available</p>
        <p class="text-2xl font-bold text-emerald-400 mt-1">GH₵{{ number_format($available ?? 0, 2) }}</p>
    </div>
</div>

<div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-6 mb-6">
    <h3 class="text-sm font-semibold text-slate-800 mb-3">Request Withdrawal</h3>
    <form method="POST" action="{{ route('user.shop-profits.withdraw') }}">
        @csrf
        <div class="flex gap-3">
            <input type="number" name="amount" step="0.01" min="1" max="{{ $available ?? 0 }}" placeholder="Amount (GH₵)" required class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] outline-none">
            <button type="submit" class="px-5 py-2 bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-bold rounded-xl transition shadow-md" onclick="return confirm('Request withdrawal?')"><x-heroicon-o-banknotes class="w-4 h-4" />Withdraw</button>
        </div>
    </form>
</div>

<div class="bg-white border border-slate-100 shadow-sm rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100"><h3 class="text-sm font-semibold text-slate-800">Withdrawal History</h3></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-100"><th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">ID</th><th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Amount</th><th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Status</th><th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Date</th></tr></thead>
            <tbody>
                @forelse($withdrawals as $w)
                    <tr class="border-b border-slate-100 hover:bg-orange-50/20">
                        <td class="px-5 py-3 text-slate-800">#{{ $w->id }}</td>
                        <td class="px-5 py-3 text-slate-600">GH₵{{ number_format($w->amount, 2) }}</td>
                        <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $w->status === 'delivered' ? 'bg-emerald-50 text-emerald-600' : ($w->status === 'rejected' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600') }}">{{ ucfirst($w->status) }}</span></td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $w->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500 text-sm">No withdrawals yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
