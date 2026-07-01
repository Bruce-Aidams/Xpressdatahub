�@extends('layouts.admin')
@section('page-title', 'Agents Management')
@section('page-description', 'Manage platform agents, super-agents and dealers')
@section('content')

@php
    $allCount = $agents->total();
    $activeCount = \App\Models\Agent::where('status', 'active')->count();
    $suspendedCount = \App\Models\Agent::where('status', 'suspended')->count();
@endphp

<div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-5 sm:mb-6">
    <div class="bg-white border border-slate-100 rounded-xl px-3 sm:px-4 py-1.5 sm:py-2 shadow-sm flex items-center gap-1.5 sm:gap-2">
        <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#2563EB] inline-block"></span>
        <span class="text-[10px] sm:text-xs font-bold text-slate-500">All</span>
        <span class="text-xs sm:text-sm font-black text-slate-800 ml-0.5 sm:ml-1">{{ $allCount }}</span>
    </div>
    <div class="bg-white border border-slate-100 rounded-xl px-3 sm:px-4 py-1.5 sm:py-2 shadow-sm flex items-center gap-1.5 sm:gap-2">
        <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-emerald-500 inline-block"></span>
        <span class="text-[10px] sm:text-xs font-bold text-slate-500">Active</span>
        <span class="text-xs sm:text-sm font-black text-slate-800 ml-0.5 sm:ml-1">{{ $activeCount }}</span>
    </div>
    <div class="bg-white border border-slate-100 rounded-xl px-3 sm:px-4 py-1.5 sm:py-2 shadow-sm flex items-center gap-1.5 sm:gap-2">
        <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-red-400 inline-block"></span>
        <span class="text-[10px] sm:text-xs font-bold text-slate-500">Suspended</span>
        <span class="text-xs sm:text-sm font-black text-slate-800 ml-0.5 sm:ml-1">{{ $suspendedCount }}</span>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center gap-3">
        <h3 class="text-xs sm:text-sm font-bold text-slate-800">Agent List</h3>
        <form method="GET" class="flex flex-wrap gap-2 sm:ml-auto w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search agents..."
                   class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] w-full sm:w-44">
            <select name="role" class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB]">
                <option value="">All Roles</option>
                <option value="agent"       {{ request('role') === 'agent'       ? 'selected' : '' }}>Agent</option>
                <option value="super_agent" {{ request('role') === 'super_agent' ? 'selected' : '' }}>Super Agent</option>
                <option value="dealers"     {{ request('role') === 'dealers'     ? 'selected' : '' }}>Dealer</option>
            </select>
            <select name="status" class="px-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB]">
                <option value="">All Status</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
            <button type="submit" class="px-3 sm:px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs sm:text-sm font-bold rounded-xl transition flex items-center gap-1.5">
                <x-heroicon-o-magnifying-glass class="w-5 h-5" />
            </button>
        </form>
        <button onclick="document.getElementById('addAgentModal').classList.remove('hidden')"
                class="px-3 sm:px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs sm:text-sm font-bold rounded-xl transition flex items-center gap-1.5 sm:gap-2 shrink-0">
            <x-heroicon-o-plus class="w-5 h-5" /> <span class="hidden sm:inline">Add</span> Agent
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-slate-50/60 border-b border-slate-100">
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Agent</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden sm:table-cell">Email</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden md:table-cell">Role</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Balance</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden md:table-cell">Orders</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 hidden sm:table-cell">Status</th>
                    <th class="px-3 sm:px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agents as $agent)
                    @php
                        $isActive = $agent->status === 'active';
                        $isSuspended = $agent->status === 'suspended';
                    @endphp
                    <tr class="border-b border-slate-100 hover:bg-blue-50/20 transition">
                        <td class="px-3 sm:px-5 py-3 sm:py-4">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-[#2563EB]/10 flex items-center justify-center text-[#2563EB] font-black text-[10px] sm:text-xs shrink-0">
                                    {{ strtoupper(substr($agent->username, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-xs leading-tight">{{ $agent->username }}</p>
                                    <p class="text-[9px] sm:text-[10px] text-slate-400">#{{ $agent->id }}</p>
                                    <div class="flex items-center gap-1 sm:hidden mt-0.5">
                                        @if($isActive)
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        @elseif($isSuspended)
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        @endif
                                        <span class="text-[9px] font-bold text-slate-500">{{ ucfirst($agent->status) }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 text-slate-500 text-[10px] sm:text-xs hidden sm:table-cell">{{ $agent->email }}</td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 hidden md:table-cell">
                            <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full text-[9px] sm:text-[10px] font-bold bg-blue-50 text-blue-600">{{ ucfirst(str_replace('_', ' ', $agent->role)) }}</span>
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 font-bold text-slate-800 text-xs sm:text-sm">GH&#8373;{{ number_format($agent->balance, 2) }}</td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 text-slate-600 font-semibold text-xs sm:text-sm hidden md:table-cell">{{ $agent->orders_count ?? 0 }}</td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 hidden sm:table-cell">
                            <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full text-[9px] sm:text-[10px] font-bold
                                {{ $isActive ? 'text-emerald-600 bg-emerald-50' :
                                   ($isSuspended ? 'text-red-600 bg-red-50' : 'text-amber-600 bg-amber-50') }}">
                                {{ ucfirst($agent->status) }}
                            </span>
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4">
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <a href="{{ route('admin.agents.show', $agent->id) }}"
                                   class="text-slate-400 hover:text-[#2563EB] transition" title="View">
                                    <x-heroicon-o-eye class="w-5 h-5" />
                                </a>
                                <button onclick="openEditModal({{ $agent->id }}, '{{ $agent->first_name }}', '{{ $agent->last_name }}', '{{ $agent->email }}', '{{ $agent->phone }}', '{{ $agent->role }}', {{ $agent->balance }})"
                                        class="text-slate-400 hover:text-blue-500 transition" title="Edit">
                                    <x-heroicon-o-pencil class="w-5 h-5" />
                                </button>
                                <form method="POST" action="{{ route('admin.agents.toggle-status', $agent->id) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="{{ $isActive ? 'suspended' : 'active' }}">
                                    <button type="submit"
                                            class="transition text-xs sm:text-sm {{ $isActive ? 'text-slate-400 hover:text-red-500' : 'text-slate-400 hover:text-emerald-500' }}"
                                            title="{{ $isActive ? 'Suspend' : 'Activate' }}">
                                        <x-dynamic-component :component="$isActive ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle'" class="w-5 h-5" />
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.agents.destroy', $agent->id) }}" class="inline" onsubmit="return confirm('Delete agent {{ $agent->username }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-500 transition" title="Delete">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-16 text-center text-slate-400 font-medium">No agents found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-slate-100">{{ $agents->withQueryString()->links('pagination::tailwind') }}</div>
</div>

{{-- Add Agent Modal --}}
<div id="addAgentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md mx-4 p-7">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-black text-slate-800">Add New Agent</h3>
                <p class="text-xs text-slate-400 mt-0.5">Fill in the details to create an account</p>
            </div>
            <button onclick="document.getElementById('addAgentModal').classList.add('hidden')"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <form method="POST" action="{{ route('admin.agents.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">First Name *</label>
                    <input type="text" name="first_name" required placeholder="e.g. John"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Last Name *</label>
                    <input type="text" name="last_name" required placeholder="e.g. Doe"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Phone *</label>
                <input type="text" name="phone" required placeholder="e.g. 0240000000"
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Username *</label>
                <input type="text" name="username" required placeholder="e.g. john_doe"
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Email *</label>
                <input type="email" name="email" required placeholder="agent@example.com"
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Password *</label>
                <input type="password" name="password" required placeholder="Min 8 characters"
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Role *</label>
                    <select name="role" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                        <option value="agent">Agent</option>
                        <option value="super_agent">Super Agent</option>
                        <option value="dealers">Dealer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Initial Balance</label>
                    <input type="number" name="balance" value="0" step="0.01" min="0"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addAgentModal').classList.add('hidden')"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl transition">
                    Create Agent
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Agent Modal --}}
<div id="editAgentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md mx-4 p-7">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-black text-slate-800">Edit Agent</h3>
                <p class="text-xs text-slate-400 mt-0.5">Update agent information</p>
            </div>
            <button onclick="document.getElementById('editAgentModal').classList.add('hidden')"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <form id="editAgentForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">First Name</label>
                    <input type="text" name="first_name" id="edit_first_name" required
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Last Name</label>
                    <input type="text" name="last_name" id="edit_last_name" required
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Phone</label>
                <input type="text" name="phone" id="edit_phone" required
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Email</label>
                <input type="email" name="email" id="edit_email" required
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Role</label>
                    <select name="role" id="edit_role" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                        <option value="agent">Agent</option>
                        <option value="super_agent">Super Agent</option>
                        <option value="dealers">Dealer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Balance (GH&#8373;)</label>
                    <input type="number" name="balance" id="edit_balance" step="0.01" min="0"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('editAgentModal').classList.add('hidden')"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, firstName, lastName, email, phone, role, balance) {
    document.getElementById('editAgentForm').action = '/admin/agents/' + id;
    document.getElementById('edit_first_name').value = firstName;
    document.getElementById('edit_last_name').value = lastName;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_balance').value = balance;
    document.getElementById('editAgentModal').classList.remove('hidden');
}
</script>
@endsection