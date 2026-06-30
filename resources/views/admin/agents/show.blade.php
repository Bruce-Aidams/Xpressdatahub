�@extends('layouts.admin')
@section('page-title', $agent->username)
@section('page-description', 'Agent profile and details')
@section('content')

<a href="{{ route('admin.agents.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-[#2563EB] transition mb-4">
    <x-heroicon-o-arrow-left class="w-5 h-5" /> Back to Agents
</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-6">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 rounded-full bg-[#2563EB]/10 flex items-center justify-center text-[#2563EB] font-black text-lg">
                {{ strtoupper(substr($agent->username, 0, 2)) }}
            </div>
            <div>
                <h3 class="text-base font-black text-slate-800">{{ $agent->first_name }} {{ $agent->last_name }}</h3>
                <p class="text-xs text-slate-400">@{{ $agent->username }} &middot; #{{ $agent->id }}</p>
            </div>
        </div>

        @php
            $isActive = $agent->status === 'active';
            $isSuspended = $agent->status === 'suspended';
        @endphp
        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold
            {{ $isActive ? 'text-emerald-600 bg-emerald-50' :
               ($isSuspended ? 'text-red-600 bg-red-50' : 'text-amber-600 bg-amber-50') }}">
            {{ ucfirst($agent->status) }}
        </span>

        <div class="mt-5 space-y-3">
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400 text-xs">Email</span>
                <span class="text-slate-700 font-medium text-xs">{{ $agent->email }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400 text-xs">Phone</span>
                <span class="text-slate-700 font-medium text-xs">{{ $agent->phone ?? 'N/A' }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400 text-xs">Role</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600">{{ ucfirst(str_replace('_', ' ', $agent->role)) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400 text-xs">Balance</span>
                <span class="text-slate-800 font-black text-sm">GH₵{{ number_format($agent->balance, 2) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400 text-xs">Referral Code</span>
                <span class="text-slate-700 font-mono text-xs">{{ $agent->referral_code ?? 'N/A' }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400 text-xs">Joined</span>
                <span class="text-slate-700 text-xs">{{ $agent->created_at?->format('M d, Y H:i') ?? 'N/A' }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400 text-xs">Last Login IP</span>
                <span class="text-slate-700 font-mono text-xs">{{ $agent->last_login_ip ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <form method="POST" action="{{ route('admin.agents.toggle-status', $agent->id) }}" class="flex-1">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="{{ $isActive ? 'suspended' : 'active' }}">
                <button type="submit" class="w-full py-2 text-xs font-bold rounded-xl border transition
                    {{ $isActive ? 'border-red-200 text-red-500 hover:bg-red-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}">
                    {{ $isActive ? 'Suspend Agent' : 'Activate Agent' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.agents.destroy', $agent->id) }}" class="flex-1" onsubmit="return confirm('Permanently delete this agent? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-2 text-xs font-bold rounded-xl border border-slate-200 text-slate-400 hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition">
                    Delete Agent
                </button>
            </form>
        </div>
    </div>

    {{-- Stats + Orders --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 text-center">
                <p class="text-2xl font-black text-slate-800">{{ $agent->orders_count ?? 0 }}</p>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">Total Orders</p>
            </div>
            <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 text-center">
                <p class="text-2xl font-black text-slate-800">{{ $agent->payments_count ?? 0 }}</p>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">Payments</p>
            </div>
            <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 text-center">
                <p class="text-2xl font-black text-slate-800">{{ $agent->referrer_commissions_count ?? 0 }}</p>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">Referral Earnings</p>
            </div>
            <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 text-center">
                <p class="text-2xl font-black text-slate-800">{{ $agent->referred_commissions_count ?? 0 }}</p>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">Referrals Made</p>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Recent Orders</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-slate-50/60 border-b border-slate-100">
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">ID</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Phone</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Network</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Amount</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            @php $s = $order->status; @endphp
                            <tr class="border-b border-slate-100 hover:bg-blue-50/20 transition">
                                <td class="px-5 py-3 font-black text-slate-800">#{{ $order->id }}</td>
                                <td class="px-5 py-3 text-slate-600 text-xs">{{ $order->phone_number ?? 'N/A' }}</td>
                                <td class="px-5 py-3"><x-network-badge :network="$order->network_type ?? 'N/A'" /></td>
                                <td class="px-5 py-3 font-bold text-slate-800 text-xs">GH₵{{ number_format($order->amount, 2) }}</td>
                                <td class="px-5 py-3">
                                    <x-status-badge :status="$s" />
                                </td>
                                <td class="px-5 py-3 text-slate-400 text-xs">{{ $order->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400 text-xs">No orders yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Balance History --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Balance History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-slate-50/60 border-b border-slate-100">
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Reason</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Amount</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Balance After</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBalance as $record)
                            <tr class="border-b border-slate-100 hover:bg-blue-50/20 transition">
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                        {{ $record->change_amount >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50' }}">
                                        {{ ucfirst($record->reason) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-bold text-xs {{ $record->change_amount >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ $record->change_amount >= 0 ? '+' : '-' }}GH₵{{ number_format(abs($record->change_amount), 2) }}
                                </td>
                                <td class="px-5 py-3 text-slate-700 font-semibold text-xs">GH₵{{ number_format($record->balance_after, 2) }}</td>
                                <td class="px-5 py-3 text-slate-400 text-xs">{{ $record->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400 text-xs">No balance history</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
