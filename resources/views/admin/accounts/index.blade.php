¿@extends('layouts.admin')
@section('page-title', 'Account Management')
@section('page-description', 'Activate or suspend user accounts')
@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-black text-slate-800">Accounts</h2>
    <p class="text-sm text-slate-400 mt-1">Manage user accounts, roles, and balances</p>
</div>

<form method="GET" class="bg-white border border-slate-100/80 rounded-2xl p-4 shadow-sm mb-6">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search accounts..."
                   class="w-full pl-9 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
        </div>
        <button type="submit"
                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold rounded-xl transition flex items-center gap-2">
            <x-heroicon-o-funnel class="w-4 h-4" />
            <span class="hidden sm:inline">Filter</span>
        </button>
    </div>
</form>

<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">User</th>
                    <th class="px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Role</th>
                    <th class="px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Balance</th>
                    <th class="px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                    <th class="px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounts as $account)
                    @php $active = $account->is_active ?? true; @endphp
                    <tr class="border-b border-slate-100 hover:bg-blue-50/20 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#2563EB] to-[#1D4ED8] flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-sm">
                                    {{ strtoupper(substr($account->username, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-xs">{{ $account->username }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $account->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100 px-2.5 py-1">
                                {{ ucfirst(str_replace('_', ' ', $account->role)) }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="font-bold text-slate-800 text-xs">GHâ‚µ{{ number_format($account->balance ?? 0, 2) }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $active ? 'text-emerald-600 bg-emerald-50 border border-emerald-100' : 'text-red-600 bg-red-50 border border-red-100' }}">
                                {{ $active ? 'Active' : 'Suspended' }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('admin.accounts.toggle', $account->id) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="{{ $active ? 'suspended' : 'active' }}">
                                    <button type="submit"
                                            class="px-2.5 py-1.5 text-[10px] font-bold rounded-lg border transition flex items-center gap-1
                                                   {{ $active ? 'border-red-200 text-red-500 hover:bg-red-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}">
                                        @if($active)
                                            <x-heroicon-o-no-symbol class="w-3 h-3" />
                                            Suspend
                                        @else
                                            <x-heroicon-o-check-circle class="w-3 h-3" />
                                            Activate
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center">
                                    <x-heroicon-o-user-group class="w-7 h-7 text-slate-300" />
                                </div>
                                <p class="text-sm font-medium text-slate-400">No accounts found</p>
                                <p class="text-xs text-slate-300">Try adjusting your search criteria</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $accounts->withQueryString()->links('pagination::tailwind') }}
    </div>
</div>
@endsection
