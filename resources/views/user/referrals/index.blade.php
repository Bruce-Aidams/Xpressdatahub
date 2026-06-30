�@extends('layouts.user')
@section('title', 'Referrals')
@section('page-title', 'Referrals')
@section('page-description', 'Manage your referrals and earnings')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-black text-slate-800">Referrals</h1>
    <p class="text-sm text-slate-400 mt-1">Manage your referrals and earnings</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-slate-400 font-medium">Total Referrals</p>
            <p class="text-2xl font-black text-slate-800 mt-1">{{ $totalReferrals ?? 0 }}</p>
        </div>
        <div class="bg-blue-50 p-3 rounded-xl">
            <x-heroicon-o-users class="w-6 h-6 text-blue-500" />
        </div>
    </div>
    <div class="bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-slate-400 font-medium">Total Earnings</p>
            <p class="text-2xl font-black text-slate-800 mt-1">GH₵{{ number_format($totalEarnings ?? 0, 2) }}</p>
        </div>
        <div class="bg-[#EA580C]/10 p-3 rounded-xl">
            <x-heroicon-o-currency-dollar class="w-6 h-6 text-[#EA580C]" />
        </div>
    </div>
</div>

<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-800">Your Referral Link</h3>
    </div>
    <div class="p-6">
        <div class="flex gap-2">
            <input
                type="text"
                id="referralLink"
                value="{{ $referralLink ?? '' }}"
                readonly
                class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono"
            />
            <button
                type="button"
                onclick="navigator.clipboard.writeText(document.getElementById('referralLink').value)"
                class="bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-bold rounded-xl px-4 py-2.5 transition flex items-center gap-1.5"
            >
                <x-heroicon-o-clipboard-document class="w-4 h-4" />
                Copy
            </button>
        </div>
        <p class="text-xs text-slate-400 mt-2">Share this link with others and earn commissions on their transactions.</p>
    </div>
</div>

<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-800">Referred Users</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">User</th>
                    <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Joined</th>
                    <th class="text-left px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Earnings</th>
                </tr>
            </thead>
            <tbody>
                @forelse($referrals as $ref)
                    <tr class="border-b border-slate-100 hover:bg-orange-50/20 transition">
                        <td class="px-5 py-3 text-slate-600">{{ $ref->referred->username ?? 'N/A' }}</td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $ref->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                        <td class="px-5 py-3 text-[#EA580C] font-semibold">GH&#8373;{{ number_format($ref->commission_amount ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="bg-slate-100 p-3 rounded-full mb-3">
                                    <x-heroicon-o-users class="w-8 h-8 text-slate-400" />
                                </div>
                                <p class="text-sm text-slate-500 font-medium">No referrals yet</p>
                                <p class="text-xs text-slate-400 mt-1">Share your referral link to get started</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection