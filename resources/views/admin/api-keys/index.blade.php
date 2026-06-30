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
                        <td class="px-5 py-3 text-slate-600 font-mono text-xs">{{ substr($key->api_key, 0, 12) }}...</td>
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
