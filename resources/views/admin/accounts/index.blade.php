@extends('layouts.admin')
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
            <button onclick="openBulkCreditDebit('credit')" class="px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <x-heroicon-o-arrow-up class="w-3.5 h-3.5" />
                Credit
            </button>
            <button onclick="openBulkCreditDebit('debit')" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <x-heroicon-o-arrow-down class="w-3.5 h-3.5" />
                Debit
            </button>
            <button onclick="openBulkStatusModal()" class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                Status
            </button>
            <button onclick="clearSelection()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                Deselect
            </button>
        </div>
    </div>
</div>

<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-5 py-3 w-10">
                        <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB] cursor-pointer">
                    </th>
                    <th class="px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">User</th>
                    <th class="px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Role</th>
                    <th class="px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Balance</th>
                    <th class="px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                    <th class="px-5 py-3 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounts as $account)
                    @php $active = $account->is_active ?? ($account->status === 'active'); @endphp
                    <tr class="border-b border-slate-100 hover:bg-blue-50/20 transition">
                        <td class="px-5 py-4">
                            <input type="checkbox" name="agent_ids[]" value="{{ $account->id }}" class="row-checkbox w-4 h-4 rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB] cursor-pointer">
                        </td>
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
                            <span class="font-bold text-slate-800 text-xs">GH&#8373;{{ number_format($account->balance ?? 0, 2) }}</span>
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
                        <td colspan="6" class="px-5 py-16 text-center">
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

{{-- Bulk Credit/Debit Modal --}}
<div id="bulkCreditDebitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md mx-4 p-7">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 id="creditDebitModalTitle" class="text-base font-black text-slate-800">Credit Account</h3>
                <p class="text-xs text-slate-400 mt-0.5">Add funds to selected accounts</p>
            </div>
            <button onclick="closeBulkCreditDebitModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <form id="bulkCreditDebitForm" method="POST" action="{{ route('admin.accounts.bulk.credit') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="type" id="bulkCreditDebitType">
            <div id="bulkCreditDebitIdsContainer"></div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Amount (GH&#8373;) *</label>
                <input type="number" name="amount" required step="0.01" min="0.01" placeholder="0.00"
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Description</label>
                <input type="text" name="description" placeholder="e.g. Bonus credit, Correction"
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeBulkCreditDebitModal()"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" id="creditDebitSubmitBtn"
                        class="flex-1 py-2.5 text-white text-sm font-bold rounded-xl transition">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Status Modal --}}
<div id="bulkStatusModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md mx-4 p-7">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-black text-slate-800">Change Account Status</h3>
                <p class="text-xs text-slate-400 mt-0.5">Update status for selected accounts</p>
            </div>
            <button onclick="closeBulkStatusModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <form id="bulkStatusForm" method="POST" action="{{ route('admin.accounts.bulk.status') }}" class="space-y-4">
            @csrf
            <div id="bulkStatusIdsContainer"></div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status *</label>
                <select name="status" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-slate-50 focus:outline-none focus:border-[#2563EB] transition">
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeBulkStatusModal()"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl transition">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const bulkBar = document.getElementById('bulkActionBar');
    const countEl = document.getElementById('selectedCount');
    const bulkCreditDebitModal = document.getElementById('bulkCreditDebitModal');
    const bulkStatusModal = document.getElementById('bulkStatusModal');

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

    function setHiddenInputs(containerId, ids) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'agent_ids[]';
            input.value = id;
            container.appendChild(input);
        });
    }

    window.openBulkCreditDebit = function(type) {
        const ids = getSelectedIds();
        if (ids.length === 0) return;

        const title = document.getElementById('creditDebitModalTitle');
        const submitBtn = document.getElementById('creditDebitSubmitBtn');
        const typeField = document.getElementById('bulkCreditDebitType');
        const form = document.getElementById('bulkCreditDebitForm');

        if (type === 'credit') {
            title.textContent = 'Credit Account';
            title.className = 'text-base font-black text-emerald-600';
            submitBtn.textContent = 'Credit ' + ids.length + ' account(s)';
            submitBtn.className = 'flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold rounded-xl transition';
            form.action = '{{ route("admin.accounts.bulk.credit") }}';
            typeField.value = 'credit';
        } else {
            title.textContent = 'Debit Account';
            title.className = 'text-base font-black text-red-600';
            submitBtn.textContent = 'Debit ' + ids.length + ' account(s)';
            submitBtn.className = 'flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl transition';
            form.action = '{{ route("admin.accounts.bulk.debit") }}';
            typeField.value = 'debit';
        }

        setHiddenInputs('bulkCreditDebitIdsContainer', ids);
        bulkCreditDebitModal.classList.remove('hidden');
    };

    window.closeBulkCreditDebitModal = function() {
        bulkCreditDebitModal.classList.add('hidden');
    };

    window.openBulkStatusModal = function() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        const form = document.getElementById('bulkStatusForm');
        form.action = '{{ route("admin.accounts.bulk.status") }}';
        setHiddenInputs('bulkStatusIdsContainer', ids);
        bulkStatusModal.classList.remove('hidden');
    };

    window.closeBulkStatusModal = function() {
        bulkStatusModal.classList.add('hidden');
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBulkCreditDebitModal();
            closeBulkStatusModal();
        }
    });
})();
</script>
@endpush
