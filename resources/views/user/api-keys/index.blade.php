@extends('layouts.user')
@section('title', 'API Keys')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-black text-slate-800">API Keys</h1>
    <p class="text-sm text-slate-400 mt-1">Manage your API access keys for external integrations.</p>
</div>

<div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800">Your API Keys</h3>
        <button
            onclick="document.getElementById('createKeyModal').classList.remove('hidden')"
            class="bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-bold rounded-xl px-4 py-2 transition shadow-md shadow-orange-500/10 flex items-center gap-1.5"
        >
            <x-heroicon-o-plus class="w-4 h-4" />
            Create API Key
        </button>
    </div>

    <div class="divide-y divide-slate-100">
        @forelse($apiKeys as $key)
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-bold text-slate-800">{{ $key->name ?? 'Unnamed Key' }}</p>
                    </div>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="font-mono text-sm text-slate-600 bg-slate-50 px-3 py-1 rounded-lg">
                            ••••••{{ substr($key->api_key, -6) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Created {{ $key->created_at?->format('M d, Y') ?? 'N/A' }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <x-status-badge :active="$key->is_active" />
                    @if($key->is_active)
                        <form method="POST" action="{{ route('user.api-keys.revoke', $key->id) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-xl transition flex items-center gap-1"
                                onclick="return confirm('Revoke this key?')"
                            >
                                <x-heroicon-o-trash class="w-4 h-4" />
                                Revoke
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                    <x-heroicon-o-key class="w-8 h-8 text-slate-400" />
                </div>
                <h4 class="text-lg font-bold text-slate-800">No API keys yet</h4>
                <p class="text-sm text-slate-400 mt-1 max-w-sm mx-auto">Create your first API key to start integrating with external services.</p>
                <button
                    onclick="document.getElementById('createKeyModal').classList.remove('hidden')"
                    class="mt-6 bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-bold rounded-xl px-5 py-2.5 transition shadow-md shadow-orange-500/10"
                >
                    Create Your First Key
                </button>
            </div>
        @endforelse
    </div>
</div>

{{-- Create Key Modal --}}
<div id="createKeyModal" class="hidden fixed inset-0 bg-black/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Generate New API Key</h3>
            <button onclick="document.getElementById('createKeyModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <form method="POST" action="{{ route('user.api-keys.store') }}">
            @csrf
            <div class="p-6">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Key Name *</label>
                <input
                    type="text"
                    name="name"
                    required
                    placeholder="e.g. Production API Key"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#EA580C] focus:ring-2 focus:ring-[#EA580C]/10 outline-none transition"
                >
                <p class="text-[10px] text-slate-400 mt-1.5">A friendly name to identify this key</p>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                <button
                    type="button"
                    onclick="document.getElementById('createKeyModal').classList.add('hidden')"
                    class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50 transition"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-bold rounded-xl px-4 py-2 transition shadow-md shadow-orange-500/10 flex items-center gap-1.5"
                >
                    <x-heroicon-o-key class="w-4 h-4" />
                    Generate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
