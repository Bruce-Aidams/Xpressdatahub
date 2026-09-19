@extends('layouts.admin')
@section('page-title', 'API Keys Management')
@section('page-description', 'Manage API keys for integrations')
@section('content')
<div class="bg-white border border-slate-100 shadow-sm rounded-2xl overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-800">API Keys</h3>
        <button onclick="document.getElementById('createKeyModal').classList.remove('hidden')" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-medium rounded-xl transition flex items-center gap-2"><x-heroicon-o-plus class="w-5 h-5" /> Generate New Key</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-100">
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">ID</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Name</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Key</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Status</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Created</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Actions</th>
            </tr></thead>
            <tbody>
                @forelse($apiKeys as $key)
                    <tr class="border-b border-slate-100 hover:bg-blue-50/20">
                        <td class="px-5 py-3 text-slate-800">#{{ $key->id }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $key->name ?? 'N/A' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs text-slate-600 bg-slate-50 px-2 py-1 rounded border border-slate-200">
                                    ••••••{{ substr($key->api_key, -6) }}
                                </span>
                                <button type="button" onclick="document.getElementById('viewKeyModal-{{ $key->id }}').classList.remove('hidden')" class="px-2.5 py-1 text-[10px] font-bold text-[#2563EB] bg-blue-50 hover:bg-blue-100 rounded transition">
                                    View Details
                                </button>
                            </div>
                        </td>
                        <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $key->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">{{ $key->is_active ? 'Active' : 'Revoked' }}</span></td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $key->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.api-keys.revoke', $key->id) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-600 text-xs" onclick="return confirm('Revoke this key?')"><x-heroicon-o-no-symbol class="w-4 h-4" />Revoke</button>
                            </form>
                        </td>
                    </tr>
                    
                    {{-- View Key Modal --}}
                    <div id="viewKeyModal-{{ $key->id }}" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-slate-800">API Key Details</h3>
                                <button onclick="document.getElementById('viewKeyModal-{{ $key->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                </button>
                            </div>
                            <div class="p-6 space-y-6">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">API Key</label>
                                    <div class="flex items-center gap-2">
                                        <input type="password" readonly value="{{ $key->api_key }}" class="w-full font-mono text-sm text-slate-800 bg-slate-50 px-4 py-3 rounded-xl border border-slate-200 outline-none" id="modal-key-{{ $key->id }}">
                                        <button type="button" onclick="toggleVisibility('modal-key-{{ $key->id }}', this)" class="shrink-0 p-3 text-slate-400 hover:text-[#2563EB] bg-white rounded-xl border border-slate-200 shadow-sm transition" title="Toggle visibility">
                                            <x-heroicon-o-eye class="w-5 h-5" />
                                        </button>
                                        <button type="button" onclick="copyToClipboard('modal-key-{{ $key->id }}', this)" class="shrink-0 p-3 text-slate-400 hover:text-emerald-500 bg-white rounded-xl border border-slate-200 shadow-sm transition" title="Copy to clipboard">
                                            <x-heroicon-o-clipboard-document class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">API Secret</label>
                                    <div class="flex items-center gap-2">
                                        <input type="password" readonly value="{{ $key->api_secret }}" class="w-full font-mono text-sm text-slate-800 bg-slate-50 px-4 py-3 rounded-xl border border-slate-200 outline-none" id="modal-secret-{{ $key->id }}">
                                        <button type="button" onclick="toggleVisibility('modal-secret-{{ $key->id }}', this)" class="shrink-0 p-3 text-slate-400 hover:text-[#2563EB] bg-white rounded-xl border border-slate-200 shadow-sm transition" title="Toggle visibility">
                                            <x-heroicon-o-eye class="w-5 h-5" />
                                        </button>
                                        <button type="button" onclick="copyToClipboard('modal-secret-{{ $key->id }}', this)" class="shrink-0 p-3 text-slate-400 hover:text-emerald-500 bg-white rounded-xl border border-slate-200 shadow-sm transition" title="Copy to clipboard">
                                            <x-heroicon-o-clipboard-document class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500 text-sm">No API keys found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="createKeyModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-slate-800">Generate API Key</h3>
            <button onclick="document.getElementById('createKeyModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><x-heroicon-o-x-mark class="w-5 h-5" /></button>
        </div>
        <form method="POST" action="{{ route('admin.api-keys.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 mb-1 block">Key Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:outline-none focus:border-[#2563EB]" placeholder="e.g. Mobile App Key">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 mb-1 block">Assign to Agent (ID)</label>
                    <input type="number" name="user_id" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:outline-none focus:border-[#2563EB]" placeholder="Agent ID">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="document.getElementById('createKeyModal').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-medium rounded-xl transition">Generate</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        
        if (input.type === 'password') {
            input.type = 'text';
            button.classList.add('text-[#2563EB]');
        } else {
            input.type = 'password';
            button.classList.remove('text-[#2563EB]');
        }
    }

    function copyToClipboard(inputId, button) {
        const input = document.getElementById(inputId);
        
        const originalType = input.type;
        input.type = 'text';
        
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        input.type = originalType;

        const originalColor = button.className;
        button.className = "p-1 text-emerald-500 transition";
        setTimeout(() => {
            button.className = originalColor;
        }, 1500);
    }
</script>
@endpush
