@extends('layouts.admin')
@section('page-title', 'Pricing Rules')
@section('page-description', 'Manage data bundle pricing for each agent role')
@section('content')

{{-- Bulk Action Bar --}}
<div id="bulkActionBar" class="hidden bg-white border border-slate-100/80 rounded-2xl shadow-sm mb-4 px-4 sm:px-6 py-3">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-[#2563EB]/10 flex items-center justify-center">
                <span id="selectedCount" class="text-[#2563EB] text-xs font-black">0</span>
            </div>
            <span class="text-sm font-semibold text-slate-600">selected</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="openBulkToggleModal()" class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                Toggle Status
            </button>
            <button onclick="openBulkDeleteModal()" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <x-heroicon-o-trash class="w-3.5 h-3.5" />
                Delete
            </button>
            <button onclick="clearSelection()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                Deselect
            </button>
        </div>
    </div>
</div>

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
                    <th class="px-5 py-3.5 w-10">
                        <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB] cursor-pointer">
                    </th>
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
                        <td class="px-5 py-4">
                            <input type="checkbox" name="pricing_ids[]" value="{{ $rule->id }}" class="row-checkbox w-4 h-4 rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB] cursor-pointer">
                        </td>
                        <td class="px-5 py-4 font-bold text-slate-800">{{ $rule->package_size }}</td>
                        <td class="px-5 py-4 text-slate-500 text-xs font-semibold">{{ $rule->package_size_gb }} GB</td>
                        <td class="px-5 py-4"><x-network-badge :network="$rule->network_type" /></td>
                        <td class="px-5 py-4 font-semibold text-slate-600">GH&#8373;{{ number_format($rule->cost, 2) }}</td>
                        <td class="px-5 py-4 font-bold text-[#2563EB]">GH&#8373;{{ number_format($rule->selling_price, 2) }}</td>
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
                            <div class="flex items-center gap-1">
                                <button onclick="openEditModal({{ $rule->id }}, @js($rule->package_size), @js($rule->network_type), {{ $rule->cost }}, {{ $rule->selling_price }}, @js($rule->user_role))" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 hover:text-[#2563EB] hover:bg-blue-50 transition" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.pricing.destroy', $rule->id) }}" class="inline" onsubmit="return confirm('Delete this rule?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-5 py-16 text-center text-slate-400 font-medium">No pricing rules found</td></tr>
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
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
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
                    <option value="" disabled selected>Select Network</option>
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
                    <option value="agent" selected>Agent</option>
                    <option value="all">All Roles</option>
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

{{-- Edit Pricing Modal --}}
<div id="editPricingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md mx-4 p-7">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-black text-slate-800">Edit Pricing Rule</h3>
                <p class="text-xs text-slate-400 mt-0.5">Update data bundle price</p>
            </div>
            <button onclick="document.getElementById('editPricingModal').classList.add('hidden')"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form id="editPricingForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Package Size</label>
                <input type="text" id="edit_package_size" readonly
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-500 bg-slate-100 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Network</label>
                <input type="text" id="edit_network_type" readonly
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-500 bg-slate-100 cursor-not-allowed">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Cost Price *</label>
                    <input type="number" name="cost" id="edit_cost" step="0.01" required placeholder="0.00"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Selling Price *</label>
                    <input type="number" name="selling_price" id="edit_selling_price" step="0.01" required placeholder="0.00"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">User Role</label>
                <input type="text" id="edit_user_role" readonly
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-500 bg-slate-100 cursor-not-allowed">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('editPricingModal').classList.add('hidden')"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl transition">
                    Update Rule
                </button>
            </div>
        </form>
    </div>
</div>

</div>

{{-- Bulk Toggle Confirmation Modal --}}
<div id="bulkToggleModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md mx-4 p-7">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-black text-slate-800">Toggle Pricing Status</h3>
                <p class="text-xs text-slate-400 mt-0.5">Activate/deactivate selected pricing rules</p>
            </div>
            <button onclick="closeBulkToggleModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <form id="bulkToggleForm" method="POST" action="{{ route('admin.pricing.bulk.toggle') }}" class="space-y-4">
            @csrf
            <div id="bulkToggleIdsContainer"></div>
            <p class="text-sm text-slate-600">This will toggle the active status of <span id="bulkToggleCount" class="font-bold">0</span> pricing rule(s).</p>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeBulkToggleModal()"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl transition">
                    Toggle Status
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Delete Confirmation Modal --}}
<div id="bulkDeleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md mx-4 p-7">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-black text-red-600">Delete Pricing Rules</h3>
                <p class="text-xs text-slate-400 mt-0.5">This action cannot be undone</p>
            </div>
            <button onclick="closeBulkDeleteModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.pricing.bulk.delete') }}" class="space-y-4">
            @csrf
            @method('DELETE')
            <div id="bulkDeleteIdsContainer"></div>
            <p class="text-sm text-slate-600">Are you sure you want to delete <span id="bulkDeleteCount" class="font-bold">0</span> pricing rule(s)? This action is permanent.</p>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeBulkDeleteModal()"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl transition">
                    Delete Rules
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const bulkBar = document.getElementById('bulkActionBar');
    const countEl = document.getElementById('selectedCount');
    const bulkToggleModal = document.getElementById('bulkToggleModal');
    const bulkDeleteModal = document.getElementById('bulkDeleteModal');

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    }

    function updateBulkBar() {
        const count = getSelectedIds().length;
        countEl.textContent = count;
        bulkBar.classList.toggle('hidden', count === 0);
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const total = checkboxes.length;
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            selectAll.checked = total > 0 && checked === total;
            selectAll.indeterminate = checked > 0 && checked < total;
            updateBulkBar();
        });
    });

    window.clearSelection = function() {
        selectAll.checked = false;
        selectAll.indeterminate = false;
        checkboxes.forEach(cb => cb.checked = false);
        updateBulkBar();
    };

    function setHiddenInputs(containerId, ids, name) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = id;
            container.appendChild(input);
        });
    }

    window.openBulkToggleModal = function() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        const form = document.getElementById('bulkToggleForm');
        form.action = '{{ route("admin.pricing.bulk.toggle") }}';
        document.getElementById('bulkToggleCount').textContent = ids.length;
        setHiddenInputs('bulkToggleIdsContainer', ids, 'pricing_ids[]');
        bulkToggleModal.classList.remove('hidden');
    };

    window.closeBulkToggleModal = function() {
        bulkToggleModal.classList.add('hidden');
    };

    window.openBulkDeleteModal = function() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        const form = document.getElementById('bulkDeleteForm');
        form.action = '{{ route("admin.pricing.bulk.delete") }}';
        document.getElementById('bulkDeleteCount').textContent = ids.length;
        setHiddenInputs('bulkDeleteIdsContainer', ids, 'pricing_ids[]');
        bulkDeleteModal.classList.remove('hidden');
    };

    window.closeBulkDeleteModal = function() {
        bulkDeleteModal.classList.add('hidden');
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBulkToggleModal();
            closeBulkDeleteModal();
        }
    });
})();

function openEditModal(id, packageSize, networkType, cost, sellingPrice, userRole) {
    document.getElementById('editPricingForm').action = '{{ url(config("app.admin_path") . "/pricing") }}/' + id;
    document.getElementById('edit_package_size').value = packageSize;
    document.getElementById('edit_network_type').value = networkType;
    document.getElementById('edit_cost').value = cost;
    document.getElementById('edit_selling_price').value = sellingPrice;
    document.getElementById('edit_user_role').value = userRole.charAt(0).toUpperCase() + userRole.slice(1).replace('_', ' ');
    document.getElementById('editPricingModal').classList.remove('hidden');
}
</script>
@endpush

@endsection
