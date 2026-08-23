@extends('layouts.admin')
@section('page-title', 'API Configuration')
@section('page-description', 'Manage external site API connections per network')
@section('content')
<div class="max-w-6xl">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Network API Configuration</h1>
        <p class="text-sm text-slate-400 mt-1">Connect and manage external data provider APIs for each network</p>
    </div>

    {{-- Banners --}}
    @if($isLocked)
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3 shadow-sm">
        <x-heroicon-o-lock-closed class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
        <div>
            <h4 class="text-sm font-bold text-red-800">Configurations Locked</h4>
            <p class="text-xs text-red-600 mt-1">API configurations are currently locked. You cannot add, modify, or delete any configurations.</p>
        </div>
    </div>
    @elseif(!$canAddMore)
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3 shadow-sm">
        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
        <div>
            <h4 class="text-sm font-bold text-amber-800">Maximum Connections Reached</h4>
            <p class="text-xs text-amber-600 mt-1">You have reached the maximum number of allowed API connections. You cannot add any new ones.</p>
        </div>
    </div>
    @endif

    {{-- Action Button --}}
    <div class="flex justify-end mb-6">
        <button onclick="openAddModal()" class="inline-flex items-center gap-2 {{ $isLocked || !$canAddMore ? 'bg-slate-300 cursor-not-allowed' : 'bg-[#2563EB] hover:bg-[#1D4ED8]' }} text-white text-sm font-bold rounded-xl px-6 py-2.5 transition shadow-md shadow-blue-500/10">
            <x-heroicon-o-plus class="w-5 h-5" /> Add Network API
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center"><x-heroicon-o-puzzle-piece class="w-5 h-5 text-blue-500" /></div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total APIs</p>
                    <p class="text-lg font-black text-slate-800">{{ $configs->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center"><x-heroicon-o-check-circle class="w-5 h-5 text-emerald-500" /></div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Active</p>
                    <p class="text-lg font-black text-emerald-600">{{ $configs->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center"><x-heroicon-o-x-circle class="w-5 h-5 text-red-500" /></div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Inactive</p>
                    <p class="text-lg font-black text-red-500">{{ $configs->where('is_active', false)->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center"><x-heroicon-o-server-stack class="w-5 h-5 text-amber-500" /></div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Networks</p>
                    <p class="text-lg font-black text-slate-800">{{ $configs->pluck('network_type')->unique()->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Network Cards --}}
    @forelse($configs as $config)
        @php $isActive = $config->is_active; @endphp
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden group hover:shadow-md transition-all duration-300 mb-4">
            {{-- Card Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    @if($config->network_type === 'MTN')
                        <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center"><span class="text-amber-600 font-black text-lg">M</span></div>
                    @elseif($config->network_type === 'Telecel')
                        <div class="w-12 h-12 rounded-xl bg-red-50 border border-red-100 flex items-center justify-center"><span class="text-red-600 font-black text-lg">T</span></div>
                    @elseif($config->network_type === 'AirtelTigo')
                        <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center"><span class="text-blue-600 font-black text-lg">A</span></div>
                    @else
                        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center"><x-heroicon-o-globe-alt class="w-5 h-5 text-slate-400" /></div>
                    @endif
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-800">{{ $config->network_type }}</h3>
                            @if($isActive)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100"><span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span> Live</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-400">Off</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5 font-mono truncate max-w-md">{{ $config->endpoint_url ?? 'No endpoint' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="testConnection({{ $config->id }}, '{{ $config->network_type }}')" id="test-btn-{{ $config->id }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-300 hover:text-blue-500 hover:bg-blue-50 transition opacity-0 group-hover:opacity-100" title="Test Connection">
                        <x-heroicon-o-signal class="w-5 h-5" />
                    </button>
                    <form method="POST" action="{{ route('admin.api-config.toggle', $config->id) }}" class="inline" onsubmit="return handleToggle(this, {{ $isActive ? 'true' : 'false' }}, '{{ $config->network_type }}')">
                        @csrf
                        <button type="submit" class="w-9 h-9 rounded-xl flex items-center justify-center {{ $isActive ? 'text-emerald-500 hover:bg-emerald-50' : 'text-slate-300 hover:bg-slate-50' }} transition" title="{{ $isActive ? 'Deactivate' : 'Activate' }}">
                            <x-dynamic-component :component="$isActive ? 'heroicon-o-check-badge' : 'heroicon-o-x-circle'" class="w-5 h-5" />
                        </button>
                    </form>
                    <button onclick="editConfig(this)" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-300 hover:text-[#2563EB] hover:bg-blue-50 transition opacity-0 group-hover:opacity-100" title="Edit"
                        data-id="{{ $config->id }}"
                        data-network="{{ $config->network_type }}"
                        data-name="{{ $config->api_name }}"
                        data-endpoint="{{ $config->endpoint_url }}"
                        data-status-endpoint="{{ $config->status_endpoint }}"
                        data-api-key="{{ $config->api_key }}"
                        data-api-secret="{{ $config->api_secret }}"
                        data-method="{{ $config->request_method }}"
                        data-headers="{{ $config->request_headers }}"
                        data-template="{{ $config->request_body_template }}"
                        data-success="{{ $config->response_success_field }}"
                        data-data-field="{{ $config->response_data_field }}"
                        data-error="{{ $config->response_error_field }}"
                        data-timeout="{{ $config->timeout_seconds }}"
                        data-retries="{{ $config->retry_attempts }}"
                    >
                        <x-heroicon-o-pencil class="w-5 h-5" />
                    </button>
                    <button onclick="deleteConfig({{ $config->id }}, '{{ $config->network_type }}')" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition opacity-0 group-hover:opacity-100" title="Delete">
                        <x-heroicon-o-trash class="w-5 h-5" />
                    </button>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="px-6 py-4">
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">API Name</p>
                        <p class="text-sm text-slate-700 font-medium">{{ $config->api_name ?? 'N/A' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Method</p>
                        <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-md">{{ $config->request_method ?? 'POST' }}</span>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Timeout</p>
                        <p class="text-sm text-slate-700">{{ $config->timeout_seconds ?? 30 }}s</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Retries</p>
                        <p class="text-sm text-slate-700">{{ $config->retry_attempts ?? 3 }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">API Key</p>
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm text-slate-700 font-mono" id="key-{{ $config->id }}" data-vis="0">{{ $config->api_key ? str_repeat('*', 8) . substr($config->api_key, -6) : '—' }}</span>
                            @if($config->api_key)
                                <button type="button" onclick="toggleKey({{ $config->id }}, @js($config->api_key))" class="text-slate-300 hover:text-slate-500 transition" title="Show/Hide API Key">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white border border-dashed border-slate-200 rounded-2xl p-12 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-50 flex items-center justify-center mb-4"><x-heroicon-o-puzzle-piece class="w-5 h-5 text-slate-300" /></div>
            <h3 class="text-base font-bold text-slate-700 mb-1">No API Configurations</h3>
            <p class="text-sm text-slate-400 mb-5 max-w-sm mx-auto">Add a network API to start processing data orders automatically.</p>
            <button onclick="openAddModal()" class="inline-flex items-center gap-2 {{ $isLocked || !$canAddMore ? 'bg-slate-300 cursor-not-allowed' : 'bg-[#2563EB] hover:bg-[#1D4ED8]' }} text-white text-sm font-bold rounded-xl px-6 py-2.5 transition shadow-md shadow-blue-500/10"><x-heroicon-o-plus class="w-5 h-5" /> Add Network API</button>
        </div>
    @endforelse
</div>

{{-- Add/Edit Modal --}}
<div id="apiModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto animate-fade-in">
            <form id="apiForm" method="POST" action="{{ route('admin.api-config.store') }}" onsubmit="return validateNetwork()" autocomplete="off">
                @csrf
                <input type="hidden" name="network_type" id="m_network" required>

                {{-- Modal Header --}}
                <div class="sticky top-0 bg-white z-10 flex items-center justify-between px-6 py-4 border-b border-slate-100 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                            <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-[#2563EB]" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800" id="m_title">Add Network API</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Configure the external API connection</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 transition"><x-heroicon-o-x-mark class="w-5 h-5" /></button>
                </div>

                <div class="px-6 py-5 space-y-5">

                    {{-- Network Selector --}}
                    <div id="networkSelector">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Network <span class="text-red-400">*</span></label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['MTN', 'AirtelTigo', 'Telecel'] as $net)
                                <button type="button" onclick="selectNetwork('{{ $net }}', this)" class="network-option px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold hover:border-[#2563EB] hover:text-[#2563EB] transition text-center" data-network="{{ $net }}">
                                    @if($net === 'MTN')<span class="text-amber-500 font-black">MTN</span>
                                    @elseif($net === 'Telecel')<span class="text-red-500 font-black">Telecel</span>
                                    @else<span class="text-blue-500 font-black">AirtelTigo</span>@endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- API Name --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">API Name <span class="text-red-400">*</span></label>
                        <input type="text" name="api_name" id="m_name" required placeholder="e.g vendor-pulse API" autocomplete="off" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>

                    {{-- Endpoint --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">API Endpoint <span class="text-red-400">*</span></label>
                        <input type="url" name="api_endpoint" id="m_endpoint" required placeholder="https://api.provider.com/v1/data" autocomplete="off" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>

                    {{-- Status Endpoint --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status Check Endpoint <span class="text-slate-300 font-normal">(optional)</span></label>
                        <input type="url" name="status_endpoint" id="m_status_endpoint" placeholder="Leave empty to use main endpoint" autocomplete="off" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>

                    {{-- Credentials --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">API Key <span class="text-red-400">*</span></label>
                            <input type="text" name="api_key" id="m_api_key" required placeholder="API Key" autocomplete="off" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">API Secret</label>
                            <input type="text" name="api_secret" id="m_api_secret" placeholder="Optional" autocomplete="off" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                        </div>
                    </div>

                    {{-- Method + Timeout + Retries --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Request Method</label>
                            <select name="request_method" id="m_method" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                                <option value="POST">POST</option>
                                <option value="GET">GET</option>
                                <option value="PUT">PUT</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Timeout (seconds)</label>
                            <input type="number" name="timeout_seconds" id="m_timeout" value="30" min="5" max="300" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Retry Attempts</label>
                            <input type="number" name="retry_attempts" id="m_retries" value="3" min="0" max="10" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                        </div>
                    </div>

                    {{-- Headers --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Request Headers <span class="text-slate-300 font-normal">(JSON)</span></label>
                        <textarea name="request_headers" id="m_headers" rows="3" placeholder='{"Content-Type": "application/json", "Authorization": "Bearer {api_key}"}' class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition resize-none w-full"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Use <code class="bg-slate-100 px-1 rounded">{api_key}</code> to inject the API key</p>
                    </div>

                    {{-- Body Template --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Request Body Template <span class="text-slate-300 font-normal">(JSON)</span></label>
                            <button type="button" onclick="showPlaceholders()" class="text-[10px] text-[#2563EB] hover:underline font-medium">View Placeholders</button>
                        </div>
                        <textarea name="request_body_template" id="m_template" rows="5" placeholder='{"phone": "{phone}", "network": "{network}", "package": "{package}", "amount": "{amount}"}' class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition resize-none w-full"></textarea>
                    </div>

                    {{-- Response Fields --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Response Field Mapping</label>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[10px] text-slate-400 mb-1">Success Field</label>
                                <input type="text" name="response_success_field" id="m_success" value="success" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 mb-1">Data Field</label>
                                <input type="text" name="response_data_field" id="m_data_field" value="data" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 mb-1">Error Field</label>
                                <input type="text" name="response_error_field" id="m_error" value="error" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                            </div>
                        </div>
                    </div>

                    {{-- Active --}}
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" id="m_active" value="1" checked class="h-4 w-4 text-[#2563EB] focus:ring-[#2563EB] border-slate-300 rounded">
                        <label for="m_active" class="text-sm text-slate-700 font-medium">Active (enable immediately)</label>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="sticky bottom-0 bg-white flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 rounded-b-2xl">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-700 transition">Cancel</button>
                    <button type="submit" class="bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl px-6 py-2.5 transition shadow-md shadow-blue-500/10"><span id="m_submit">Save Configuration</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Placeholders Modal --}}
<div id="placeholderModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('placeholderModal').classList.add('hidden')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md animate-fade-in">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                        <x-heroicon-o-code-bracket class="w-5 h-5 text-[#2563EB]" />
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Template Placeholders</h3>
                </div>
                <button onclick="document.getElementById('placeholderModal').classList.add('hidden')" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100"><x-heroicon-o-x-mark class="w-5 h-5" /></button>
            </div>
            <div class="px-6 py-4 space-y-2 max-h-80 overflow-y-auto">
                @foreach([
                    ['{phone}', 'Beneficiary phone number'],
                    ['{network}', 'Network type (MTN, Telecel, AirtelTigo)'],
                    ['{package}', 'Package size in MB'],
                    ['{amount}', 'Order amount'],
                    ['{payment_method}', 'Payment method (wallet)'],
                    ['{order_id}', 'Local order ID'],
                    ['{capacity}', 'Package size in GB'],
                    ['{mb}', 'Package size in MB (alias)'],
                    ['{volume}', 'Package size in MB (alias)'],
                    ['{reference}', 'Order reference'],
                    ['{api_key}', 'Injected in headers'],
                    ['{webhook}', 'Webhook callback URL'],
                ] as [$ph, $desc])
                    <div class="flex items-center gap-3 py-1.5">
                        <code class="px-2 py-0.5 bg-blue-50 text-[#2563EB] text-xs font-bold rounded-md whitespace-nowrap">{{ $ph }}</code>
                        <span class="text-xs text-slate-500">{{ $desc }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Test Connection Modal --}}
<div id="testModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeTestModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg animate-fade-in">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div id="testIcon" class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                        <x-heroicon-o-signal class="w-5 h-5 text-blue-500" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800" id="testTitle">Testing Connection</h3>
                        <p class="text-xs text-slate-400" id="testSubtitle">Sending test request...</p>
                    </div>
                </div>
                <button onclick="closeTestModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 transition"><x-heroicon-o-x-mark class="w-5 h-5" /></button>
            </div>
            <div class="px-6 py-5">
                {{-- Loading State --}}
                <div id="testLoading" class="text-center py-8">
                    <div class="w-12 h-12 mx-auto rounded-full border-4 border-slate-200 border-t-[#2563EB] animate-spin mb-4"></div>
                    <p class="text-sm text-slate-500">Sending test request to <span id="testEndpoint" class="font-mono text-slate-700"></span>...</p>
                    <p class="text-xs text-slate-400 mt-1">Timeout: <span id="testTimeout"></span>s</p>
                </div>
                {{-- Result State --}}
                <div id="testResult" class="hidden">
                    <div id="testResultBanner" class="rounded-xl p-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div id="testResultIcon" class="w-10 h-10 rounded-xl flex items-center justify-center"></div>
                            <div>
                                <p id="testResultMessage" class="text-sm font-bold"></p>
                                <p id="testResultTime" class="text-xs mt-0.5"></p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-slate-50 rounded-lg px-3 py-2"><span class="text-slate-400">Endpoint:</span> <span id="testDetailEndpoint" class="text-slate-700 font-mono ml-1"></span></div>
                            <div class="bg-slate-50 rounded-lg px-3 py-2"><span class="text-slate-400">Method:</span> <span id="testDetailMethod" class="text-slate-700 font-bold ml-1"></span></div>
                            <div class="bg-slate-50 rounded-lg px-3 py-2"><span class="text-slate-400">HTTP Code:</span> <span id="testDetailHttpCode" class="text-slate-700 font-bold ml-1"></span></div>
                            <div class="bg-slate-50 rounded-lg px-3 py-2"><span class="text-slate-400">Response Time:</span> <span id="testDetailTime" class="text-slate-700 font-bold ml-1"></span></div>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Response Body</p>
                            <pre id="testDetailResponse" class="bg-slate-900 text-emerald-400 text-[11px] font-mono rounded-xl p-3 max-h-48 overflow-y-auto whitespace-pre-wrap break-all"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Form --}}
<form id="deleteForm" method="POST" class="hidden">@csrf @method('DELETE')</form>

@endsection

@push('scripts')
<script>
    const IS_LOCKED = @json($isLocked);
    const CAN_ADD_MORE = @json($canAddMore);

    function openAddModal() {
        if (IS_LOCKED) {
            alert('API configurations are currently locked. You cannot add new configurations.');
            return;
        }
        if (!CAN_ADD_MORE) {
            alert('Maximum API connections limit reached. You cannot add any new configurations.');
            return;
        }

        document.getElementById('m_title').textContent = 'Add Network API';
        document.getElementById('m_submit').textContent = 'Save Configuration';
        document.getElementById('apiForm').reset();
        document.getElementById('apiForm').action = '{{ route("admin.api-config.store") }}';
        document.getElementById('m_network').value = '';
        document.getElementById('m_active').checked = true;
        document.getElementById('networkSelector').style.display = 'block';
        document.querySelectorAll('.network-option').forEach(b => { b.classList.remove('border-[#2563EB]','bg-blue-50'); b.classList.add('border-slate-200'); });
        document.getElementById('m_headers').value = '';
        document.getElementById('m_template').value = '';
        document.getElementById('m_success').value = 'success';
        document.getElementById('m_data_field').value = 'data';
        document.getElementById('m_error').value = 'error';
        document.getElementById('m_timeout').value = '30';
        document.getElementById('m_retries').value = '3';
        document.getElementById('apiModal').classList.remove('hidden');
    }

    function editConfig(btn) {
        if (IS_LOCKED) {
            alert('API configurations are currently locked. You cannot modify existing configurations.');
            return;
        }

        document.getElementById('m_title').textContent = 'Edit ' + btn.dataset.network + ' API';
        document.getElementById('m_submit').textContent = 'Update Configuration';
        document.getElementById('apiForm').action = '{{ route("admin.api-config.store") }}';
        document.getElementById('m_network').value = btn.dataset.network;
        document.getElementById('m_name').value = btn.dataset.name || '';
        document.getElementById('m_endpoint').value = btn.dataset.endpoint || '';
        document.getElementById('m_status_endpoint').value = btn.dataset.statusEndpoint || '';
        document.getElementById('m_api_key').value = btn.dataset.apiKey || '';
        document.getElementById('m_api_secret').value = btn.dataset.apiSecret || '';
        document.getElementById('m_method').value = btn.dataset.method || 'POST';
        document.getElementById('m_headers').value = btn.dataset.headers || '';
        document.getElementById('m_template').value = btn.dataset.template || '';
        document.getElementById('m_success').value = btn.dataset.success || 'success';
        document.getElementById('m_data_field').value = btn.dataset.dataField || 'data';
        document.getElementById('m_error').value = btn.dataset.error || 'error';
        document.getElementById('m_timeout').value = btn.dataset.timeout || 30;
        document.getElementById('m_retries').value = btn.dataset.retries || 3;
        document.getElementById('m_active').checked = true;
        document.getElementById('networkSelector').style.display = 'none';
        document.getElementById('apiModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('apiModal').classList.add('hidden');
        // Reset form so next open (Add or Edit) always starts fresh
        document.getElementById('apiForm').reset();
        document.getElementById('m_network').value = '';
        document.querySelectorAll('.network-option').forEach(b => { b.classList.remove('border-[#2563EB]','bg-blue-50'); b.classList.add('border-slate-200'); });
    }

    function validateNetwork() {
        const net = document.getElementById('m_network').value;
        if (!net) {
            alert('Please select a network type.');
            return false;
        }
        return true;
    }

    function selectNetwork(net, btn) {
        document.getElementById('m_network').value = net;
        document.querySelectorAll('.network-option').forEach(b => { b.classList.remove('border-[#2563EB]','bg-blue-50'); b.classList.add('border-slate-200'); });
        btn.classList.remove('border-slate-200');
        btn.classList.add('border-[#2563EB]','bg-blue-50');
    }

    function handleToggle(form, isActive, network) {
        if (IS_LOCKED) {
            alert('API configurations are currently locked. You cannot toggle active status.');
            return false;
        }
        return confirm((isActive ? 'Deactivate' : 'Activate') + ' the ' + network + ' API?');
    }

    function deleteConfig(id, network) {
        if (IS_LOCKED) {
            alert('API configurations are currently locked. You cannot delete configurations.');
            return;
        }
        if (!confirm('Delete the ' + network + ' API configuration? This cannot be undone.')) return;
        const f = document.getElementById('deleteForm');
        f.action = '{{ url(config("app.admin_path") . "/api-config") }}/' + id;
        f.submit();
    }

    function toggleKey(id, key) {
        const el = document.getElementById('key-' + id);
        if (el.dataset.vis === '1') {
            el.textContent = '\u2022\u2022\u2022\u2022\u2022\u2022' + key.slice(-6);
            el.dataset.vis = '0';
        } else {
            el.textContent = key;
            el.dataset.vis = '1';
        }
    }

    function showPlaceholders() { document.getElementById('placeholderModal').classList.remove('hidden'); }

    const svgSignal = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.652a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.789m13.788 0c3.808 3.808 3.808 9.981 0 13.79M12 12h.008v.007H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>';
    const svgSpinner = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 animate-spin"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" /></svg>';
    const svgCheck = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-600"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
    const svgX = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-600"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';
    const svgWarning = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-600"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>';

    function testConnection(id, network) {
        document.getElementById('testTitle').textContent = 'Testing ' + network + ' API';
        document.getElementById('testSubtitle').textContent = 'Sending test request...';
        document.getElementById('testIcon').className = 'w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center';
        document.getElementById('testIcon').innerHTML = svgSignal;
        document.getElementById('testLoading').classList.remove('hidden');
        document.getElementById('testResult').classList.add('hidden');

        const btn = document.getElementById('test-btn-' + id);
        if (btn) { btn.innerHTML = svgSpinner; btn.disabled = true; }

        document.getElementById('testModal').classList.remove('hidden');

        fetch('{{ url(config("app.admin_path") . "/api-config") }}/' + id + '/test', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'X-HTTP-Method-Override': 'POST',
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('testLoading').classList.add('hidden');
            document.getElementById('testResult').classList.remove('hidden');

            const banner = document.getElementById('testResultBanner');
            const icon = document.getElementById('testResultIcon');
            const msg = document.getElementById('testResultMessage');
            const time = document.getElementById('testResultTime');

            if (data.success) {
                banner.className = 'rounded-xl p-4 mb-4 bg-emerald-50 border border-emerald-100';
                icon.className = 'w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center';
                icon.innerHTML = svgCheck;
                msg.className = 'text-sm font-bold text-emerald-700';
                msg.textContent = data.message;
                time.className = 'text-xs mt-0.5 text-emerald-600';
                time.textContent = data.details?.response_time || '';
                document.getElementById('testSubtitle').textContent = 'Connection successful';
            } else {
                banner.className = 'rounded-xl p-4 mb-4 bg-red-50 border border-red-100';
                icon.className = 'w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center';
                icon.innerHTML = svgX;
                msg.className = 'text-sm font-bold text-red-700';
                msg.textContent = data.message;
                time.className = 'text-xs mt-0.5 text-red-500';
                time.textContent = data.details?.response_time || '';
                document.getElementById('testSubtitle').textContent = 'Connection failed';
            }

            document.getElementById('testDetailEndpoint').textContent = data.details?.endpoint || 'N/A';
            document.getElementById('testDetailMethod').textContent = data.details?.method || 'N/A';
            document.getElementById('testDetailHttpCode').textContent = data.details?.http_code || '0';
            document.getElementById('testDetailTime').textContent = data.details?.response_time || 'N/A';
            document.getElementById('testDetailResponse').textContent = data.details?.response || data.message;

            if (btn) { btn.innerHTML = svgSignal; btn.disabled = false; }
        })
        .catch(err => {
            document.getElementById('testLoading').classList.add('hidden');
            document.getElementById('testResult').classList.remove('hidden');

            const banner = document.getElementById('testResultBanner');
            const icon = document.getElementById('testResultIcon');
            const msg = document.getElementById('testResultMessage');

            banner.className = 'rounded-xl p-4 mb-4 bg-red-50 border border-red-100';
            icon.className = 'w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center';
            icon.innerHTML = svgWarning;
            msg.className = 'text-sm font-bold text-red-700';
            msg.textContent = 'Request failed: ' + err.message;
            document.getElementById('testSubtitle').textContent = 'Request failed';
            document.getElementById('testDetailResponse').textContent = err.toString();

            if (btn) { btn.innerHTML = svgSignal; btn.disabled = false; }
        });
    }

    function closeTestModal() { document.getElementById('testModal').classList.add('hidden'); }
</script>
@endpush
