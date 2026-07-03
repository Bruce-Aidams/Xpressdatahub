@extends('layouts.admin')
@section('page-title', 'Pending Approvals')
@section('page-description', 'Review and approve new user registrations')
@section('content')

<div class="mb-6">
    <div class="flex items-center gap-3 mb-1">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
            <x-heroicon-s-clock class="w-5 h-5 text-amber-500" />
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-800">Pending Approvals</h1>
            <p class="text-sm text-slate-400 mt-0.5">Review and approve new user registrations</p>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pending</p>
                <p class="text-2xl font-black text-amber-600">{{ $pendingAgents->total() }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center">
                <x-heroicon-o-clock class="w-5 h-5 text-amber-500" />
            </div>
        </div>
    </div>
    <div class="bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Total Agents</p>
                <p class="text-2xl font-black text-slate-800">{{ \App\Models\Agent::count() }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                <x-heroicon-o-users class="w-5 h-5 text-blue-500" />
            </div>
        </div>
    </div>
    <div class="bg-white border border-slate-100/80 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Approved Today</p>
                <p class="text-2xl font-black text-emerald-600">{{ \App\Models\Agent::where('is_approved', true)->whereDate('updated_at', today())->count() }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center">
                <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-500" />
            </div>
        </div>
    </div>
</div>

{{-- Pending Users Table --}}
<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-800">Awaiting Approval</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-slate-50/60 border-b border-slate-100">
                    <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">User</th>
                    <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden sm:table-cell">Contact</th>
                    <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden md:table-cell">Registered</th>
                    <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pendingAgents as $agent)
                    <tr class="hover:bg-blue-50/20 transition">
                        {{-- User --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-sm font-bold shrink-0">
                                    {{ strtoupper(substr($agent->first_name, 0, 1)) }}{{ strtoupper(substr($agent->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-xs">{{ $agent->first_name }} {{ $agent->last_name }}</p>
                                    <p class="text-[11px] text-slate-400">@{{ $agent->username }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Contact --}}
                        <td class="px-6 py-4 hidden sm:table-cell">
                            <div>
                                <p class="text-xs text-slate-600">{{ $agent->email }}</p>
                                <p class="text-[10px] text-slate-400">{{ $agent->phone ?? 'N/A' }}</p>
                            </div>
                        </td>

                        {{-- Registered --}}
                        <td class="px-6 py-4 hidden md:table-cell">
                            <p class="text-xs text-slate-500">{{ $agent->created_at->format('M d, Y') }}</p>
                            <p class="text-[10px] text-slate-400">{{ $agent->created_at->diffForHumans() }}</p>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.agents.approve', $agent->id) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition">
                                        <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.agents.reject', $agent->id) }}" class="inline" onsubmit="return confirm('Reject this user? They will be suspended.')">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                                        <x-heroicon-o-x-circle class="w-3.5 h-3.5" />
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center">
                                    <x-heroicon-o-check-circle class="w-7 h-7 text-emerald-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-500">All caught up!</p>
                                    <p class="text-xs text-slate-400 mt-1">No pending approvals at the moment.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pendingAgents->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $pendingAgents->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
