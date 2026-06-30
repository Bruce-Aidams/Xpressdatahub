�@extends('layouts.admin')
@section('page-title', 'Pricing Rules')
@section('page-description', 'Manage data bundle pricing for each agent role')
@section('content')

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800">Pricing Rules</h3>
        <button onclick="document.getElementById('addPricingModal').classList.remove('hidden')"
                class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl transition flex items-center gap-2">
            <x-heroicon-o-plus class="w-4 h-4" /> Add Rule
        </button>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-slate-50/60 border-b border-slate-100">
                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Package</th>
                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Size</th>
                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Network</th>
                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Cost Price</th>
                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Selling Price</th>
                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Margin</th>
                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Role</th>
                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pricingRules as $rule)
                    @php
                        $margin = $rule->cost > 0 ? (($rule->selling_price - $rule->cost) / $rule->cost) * 100 : 0;
                    @endphp
                    <tr class="border-b border-slate-100 hover:bg-blue-50/20 transition">
                        <td class="px-5 py-4 font-bold text-slate-800">{{ $rule->package_size }}</td>
                        <td class="px-5 py-4 text-slate-500 text-xs font-semibold">{{ $rule->package_size_gb }} GB</td>
                        <td class="px-5 py-4"><x-network-badge :network="$rule->network_type" /></td>
                        <td class="px-5 py-4 font-semibold text-slate-600">GH₵{{ number_format($rule->cost, 2) }}</td>
                        <td class="px-5 py-4 font-bold text-[#2563EB]">GH₵{{ number_format($rule->selling_price, 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="text-xs font-bold {{ $margin >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ number_format($margin, 1) }}%
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600">{{ ucfirst(str_replace('_', ' ', $rule->user_role)) }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <form method="POST" action="{{ route('admin.pricing.toggle', $rule->id) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $rule->is_active ? 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100' : 'text-red-600 bg-red-50 hover:bg-red-100' }} transition">
                                    {{ $rule->is_active ? 'Active' : 'Disabled' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-4">
                            <form method="POST" action="{{ route('admin.pricing.destroy', $rule->id) }}" class="inline" onsubmit="return confirm('Delete this rule?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-300 hover:text-red-500 transition" title="Delete">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-5 py-16 text-center text-slate-400 font-medium">No pricing rules found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-100">{{ $pricingRules->links('pagination::tailwind') }}</div>
</div>

{{-- Add Pricing Modal --}}
<div id="addPricingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md mx-4 p-7">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-black text-slate-800">Add Pricing Rule</h3>
                <p class="text-xs text-slate-400 mt-0.5">Configure a new data bundle price</p>
            </div>
            <button onclick="document.getElementById('addPricingModal').classList.add('hidden')"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        </div>
        <form method="POST" action="{{ route('admin.pricing.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Package Size *</label>
                <input type="text" name="package_size" required placeholder="e.g. 1 GB, 500 MB"
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Network Type *</label>
                <select name="network_type" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                    <option value="all">All Networks</option>
                    <option value="MTN">MTN</option>

                    <option value="AirtelTigo">AirtelTigo</option>
                    <option value="Telecel">Telecel</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Cost Price *</label>
                    <input type="number" name="cost" step="0.01" required placeholder="0.00"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Selling Price *</label>
                    <input type="number" name="selling_price" step="0.01" required placeholder="0.00"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">User Role *</label>
                <select name="user_role" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                    <option value="all">All Roles</option>
                    <option value="agent">Agent</option>
                    <option value="super_agent">Super Agent</option>
                    <option value="dealers">Dealers</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addPricingModal').classList.add('hidden')"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl transition">
                    Create Rule
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
