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

    {{-- Action Button --}}
    <div class="flex justify-end mb-6">
        <button onclick="openAddModal()" class="inline-flex items-center gap-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl px-6 py-2.5 transition shadow-md shadow-blue-500/10">
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
                    <form method="POST" action="{{ route('admin.api-config.toggle', $config->id) }}" class="inline" onsubmit="return confirm('{{ $isActive ? 'Deactivate' : 'Activate' }} the {{ $config->network_type }} API?')">
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
                        data-headers="{{ addslashes($config->request_headers) }}"
                        data-template="{{ addslashes($config->request_body_template) }}"
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
            <button onclick="openAddModal()" class="inline-flex items-center gap-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl px-6 py-2.5 transition shadow-md shadow-blue-500/10"><x-heroicon-o-plus class="w-5 h-5" /> Add Network API</button>
        </div>
    @endforelse

    {{-- API Documentation --}}
    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden mb-6" id="apiDocsSection">
        <button onclick="toggleDocs()" class="w-full flex items-center justify-between px-6 py-4 hover:bg-slate-50/50 transition text-left" type="button">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <x-heroicon-o-book-open class="w-5 h-5 text-indigo-500" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">API Documentation & Setup Guide</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Step-by-step guide to configure network APIs with examples</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span id="docsBadge" class="hidden px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[10px] font-bold rounded-full">Open</span>
                <x-heroicon-o-chevron-down id="docsChevron" class="w-5 h-5 text-slate-400 transition-transform duration-200" />
            </div>
        </button>

        <div id="apiDocsContent" class="hidden">
            <div class="px-6 pb-6 space-y-6 border-t border-slate-100 pt-5">

                {{-- Overview --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 text-[10px] font-black flex items-center justify-center">1</span>
                        Overview
                    </h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        The API Configuration page lets you connect external data providers (APIs) that process data orders for each network (MTN, AirtelTigo, Telecel). When a customer places a data order, the system sends the order details to the configured API endpoint, which delivers the data to the customer's phone.
                    </p>
                    <div class="mt-3 p-3 bg-amber-50 border border-amber-100 rounded-xl text-xs text-amber-700 flex items-start gap-2">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
                        <span>Each network can only have <strong>one active API configuration</strong>. Adding a new config for an existing network will update the existing one.</span>
                    </div>
                </div>

                {{-- Step-by-Step --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 text-[10px] font-black flex items-center justify-center">2</span>
                        Adding a Network API — Step by Step
                    </h4>
                    <div class="space-y-3">
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                                <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">1</span>
                                <span class="text-sm font-semibold text-slate-700">Click "Add Network API"</span>
                            </div>
                            <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed">
                                Click the <strong class="text-[#2563EB]">Add Network API</strong> button at the top-right of the page. A modal form will open with all configuration fields.
                            </div>
                        </div>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                                <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">2</span>
                                <span class="text-sm font-semibold text-slate-700">Select the Network</span>
                            </div>
                            <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed">
                                Choose which network this API is for: <strong>MTN</strong>, <strong>AirtelTigo</strong>, or <strong>Telecel</strong>. This determines which orders are routed to this API. You can only have one active API per network.
                            </div>
                        </div>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                                <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">3</span>
                                <span class="text-sm font-semibold text-slate-700">Enter API Name</span>
                            </div>
                            <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed">
                                Give the configuration a recognizable name. This is for your reference only.
                                <div class="mt-2 p-2 bg-white border border-slate-100 rounded-lg font-mono text-[11px] text-slate-600">
                                    Examples: <code class="text-indigo-600">Hubnet API</code>, <code class="text-indigo-600">GigBundles</code>, <code class="text-indigo-600">MooreTel Data API</code>
                                </div>
                            </div>
                        </div>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                                <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">4</span>
                                <span class="text-sm font-semibold text-slate-700">Enter the API Endpoint URL</span>
                            </div>
                            <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed">
                                This is the full URL where data orders will be sent. Must be a valid HTTPS URL.
                                <div class="mt-2 p-2 bg-white border border-slate-100 rounded-lg font-mono text-[11px] text-slate-600">
                                    Example: <code class="text-indigo-600">https://api.hubnet.com/v1/data/purchase</code>
                                </div>
                                <div class="mt-2 text-[11px] text-slate-400">The system will POST order data (phone, network, package, amount) to this URL.</div>
                            </div>
                        </div>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                                <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">5</span>
                                <span class="text-sm font-semibold text-slate-700">Status Check Endpoint <span class="text-slate-400 font-normal">(optional)</span></span>
                            </div>
                            <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed">
                                If the API provider has a separate endpoint to check order status, enter it here. If left empty, the system will use the main endpoint for status checks.
                                <div class="mt-2 p-2 bg-white border border-slate-100 rounded-lg font-mono text-[11px] text-slate-600">
                                    Example: <code class="text-indigo-600">https://api.hubnet.com/v1/data/status</code>
                                </div>
                            </div>
                        </div>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                                <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">6</span>
                                <span class="text-sm font-semibold text-slate-700">Enter API Credentials</span>
                            </div>
                            <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed">
                                <strong>API Key</strong> (required): Your authentication key from the provider.<br>
                                <strong>API Secret</strong> (optional): If the provider requires a second secret for authentication.
                                <div class="mt-2 p-2 bg-white border border-slate-100 rounded-lg font-mono text-[11px] text-slate-600">
                                    Example: <code class="text-indigo-600">sk_live_abc123def456ghi789</code>
                                </div>
                                <div class="mt-2 text-[11px] text-slate-400">These are stored encrypted and never exposed in the admin panel (masked as <code>••••••••xxxxxx</code>).</div>
                            </div>
                        </div>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                                <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">7</span>
                                <span class="text-sm font-semibold text-slate-700">Configure Request Settings</span>
                            </div>
                            <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed space-y-2">
                                <div><strong>Request Method:</strong> Typically <code class="bg-slate-100 px-1 rounded">POST</code> for data purchases. Use <code class="bg-slate-100 px-1 rounded">GET</code> if the API requires query parameters.</div>
                                <div><strong>Timeout:</strong> How long (in seconds) to wait for a response before retrying. Default: <code class="bg-slate-100 px-1 rounded">30s</code>. Range: 5–300 seconds.</div>
                                <div><strong>Retry Attempts:</strong> How many times to retry on failure. Default: <code class="bg-slate-100 px-1 rounded">3</code>. Range: 0–10.</div>
                            </div>
                        </div>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                                <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">8</span>
                                <span class="text-sm font-semibold text-slate-700">Configure Request Headers</span>
                            </div>
                            <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed">
                                Enter the HTTP headers as JSON. Use <code class="bg-slate-100 px-1 rounded">{api_key}</code> to dynamically inject the API key.
                                <div class="mt-2 p-3 bg-slate-900 rounded-lg font-mono text-[11px] text-emerald-400 leading-relaxed">
<pre class="whitespace-pre-wrap m-0">{\n    "Content-Type": "application/json",\n    "Authorization": "Bearer {api_key}"\n}</pre>
                                </div>
                                <div class="mt-2 text-[11px] text-slate-400">Some APIs use <code>api_key</code> in headers instead of the body. The <code>{api_key}</code> placeholder is replaced at runtime.</div>
                            </div>
                        </div>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                                <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">9</span>
                                <span class="text-sm font-semibold text-slate-700">Configure Request Body Template</span>
                            </div>
                            <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed">
                                Define the JSON body sent to the API. Use placeholders for dynamic values. Click <strong>"View Placeholders"</strong> in the modal to see all available placeholders.
                                <div class="mt-2 p-3 bg-slate-900 rounded-lg font-mono text-[11px] text-emerald-400 leading-relaxed">
<pre class="whitespace-pre-wrap m-0">{\n    "phone": "{phone}",\n    "network": "{network}",\n    "package": "{package}",\n    "amount": "{amount}",\n    "payment_method": "{payment_method}",\n    "order_id": "{order_id}",\n    "reference": "{reference}"\n}</pre>
                                </div>
                                <div class="mt-3">
                                    <strong class="text-slate-700">Common variations by provider:</strong>
                                    <div class="mt-2 space-y-2">
                                        <div class="p-2 bg-white border border-slate-100 rounded-lg">
                                            <span class="font-semibold text-indigo-600">Provider A (field: "msisdn")</span>
                                            <div class="font-mono text-[10px] text-slate-500 mt-1">"msisdn": "{phone}", "networkId": "{network}", "bundleSize": "{mb}"</div>
                                        </div>
                                        <div class="p-2 bg-white border border-slate-100 rounded-lg">
                                            <span class="font-semibold text-indigo-600">Provider B (nested body)</span>
                                            <div class="font-mono text-[10px] text-slate-500 mt-1">"subscriber": {"msisdn": "{phone}"}, "product": {"id": "{package}"}, "metadata": {"orderRef": "{order_id}"}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                                <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">10</span>
                                <span class="text-sm font-semibold text-slate-700">Map Response Fields</span>
                            </div>
                            <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed">
                                Tell the system how to read the API's response JSON. This determines whether an order was successful or failed.
                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div class="p-2 bg-white border border-slate-100 rounded-lg">
                                        <span class="font-bold text-[10px] uppercase tracking-wider text-slate-400">Success Field</span>
                                        <div class="font-mono text-[11px] text-slate-600 mt-1">Default: <code class="text-indigo-600">success</code></div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">The JSON key that indicates success (e.g. <code>"success": true</code>)</div>
                                    </div>
                                    <div class="p-2 bg-white border border-slate-100 rounded-lg">
                                        <span class="font-bold text-[10px] uppercase tracking-wider text-slate-400">Data Field</span>
                                        <div class="font-mono text-[11px] text-slate-600 mt-1">Default: <code class="text-indigo-600">data</code></div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">The JSON key containing response data (transaction ID, balance, etc.)</div>
                                    </div>
                                    <div class="p-2 bg-white border border-slate-100 rounded-lg">
                                        <span class="font-bold text-[10px] uppercase tracking-wider text-slate-400">Error Field</span>
                                        <div class="font-mono text-[11px] text-slate-600 mt-1">Default: <code class="text-indigo-600">error</code></div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">The JSON key containing error message when the request fails</div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <strong class="text-slate-700">Example responses and matching field names:</strong>
                                    <div class="mt-2 space-y-2">
                                        <div class="p-2 bg-white border border-slate-100 rounded-lg">
                                            <span class="font-semibold text-emerald-600">Success response</span>
                                            <div class="font-mono text-[10px] text-slate-500 mt-1">{"<span class="text-emerald-600">success</span>": true, "<span class="text-blue-600">data</span>": {"transactionId": "TXN-123"}, ...}</div>
                                        </div>
                                        <div class="p-2 bg-white border border-slate-100 rounded-lg">
                                            <span class="font-semibold text-red-600">Error response</span>
                                            <div class="font-mono text-[10px] text-slate-500 mt-1">{"<span class="text-emerald-600">success</span>": false, "<span class="text-red-600">error</span>": "Insufficient balance", ...}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 p-2 bg-amber-50 border border-amber-100 rounded-lg text-[11px] text-amber-700">
                                    <strong>Note:</strong> If your API uses different field names (e.g. <code>status</code> instead of <code>success</code>, or <code>message</code> instead of <code>error</code>), update these fields to match your provider's response format.
                                </div>
                            </div>
                        </div>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                                <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">11</span>
                                <span class="text-sm font-semibold text-slate-700">Activate & Test</span>
                            </div>
                            <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed">
                                Check <strong>"Active"</strong> to enable the API immediately. After saving, hover over the config card and click the <strong class="text-blue-500">signal icon</strong> to run a test connection. This sends a sample request to verify the endpoint, credentials, and response mapping are correct.
                                <div class="mt-2 p-2 bg-emerald-50 border border-emerald-100 rounded-lg text-[11px] text-emerald-700 flex items-start gap-2">
                                    <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                                    <span>A successful test means the API is ready to process real data orders.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Placeholder Reference --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 text-[10px] font-black flex items-center justify-center">3</span>
                        Template Placeholders Reference
                    </h4>
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-left">
                                    <th class="px-4 py-2 font-bold text-slate-500">Placeholder</th>
                                    <th class="px-4 py-2 font-bold text-slate-500">Description</th>
                                    <th class="px-4 py-2 font-bold text-slate-500">Example Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach([
                                    ['{phone}', 'Beneficiary phone number', '233501234567'],
                                    ['{network}', 'Network type', 'MTN'],
                                    ['{package}', 'Package size in MB', '1024'],
                                    ['{capacity}', 'Package size in GB', '1'],
                                    ['{mb}', 'Package size in MB (alias)', '1024'],
                                    ['{volume}', 'Package size in MB (alias)', '1024'],
                                    ['{amount}', 'Order amount', '15.00'],
                                    ['{payment_method}', 'Payment method', 'wallet'],
                                    ['{order_id}', 'Local order ID', 'ORD-2026-001'],
                                    ['{reference}', 'Order reference', 'REF-ABC123'],
                                    ['{api_key}', 'API key (injected in headers)', 'sk_live_xxx'],
                                    ['{webhook}', 'Webhook callback URL', 'https://...'],
                                ] as [$ph, $desc, $example])
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-4 py-2 font-mono text-indigo-600 font-bold">{{ $ph }}</td>
                                        <td class="px-4 py-2 text-slate-500">{{ $desc }}</td>
                                        <td class="px-4 py-2 font-mono text-slate-400">{{ $example }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Full Example --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 text-[10px] font-black flex items-center justify-center">4</span>
                        Complete Example — MTN API Configuration
                    </h4>
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <div class="px-4 py-2 bg-slate-50 border-b border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Form Fields</span>
                        </div>
                        <div class="px-4 py-3 space-y-2 text-xs">
                            <div class="grid grid-cols-2 gap-2">
                                <div><span class="text-slate-400">Network:</span> <span class="font-bold text-amber-600">MTN</span></div>
                                <div><span class="text-slate-400">API Name:</span> <span class="text-slate-700">Hubnet MTN API</span></div>
                                <div><span class="text-slate-400">Endpoint:</span> <span class="font-mono text-slate-600">https://api.hubnet.com/v1/data/purchase</span></div>
                                <div><span class="text-slate-400">Status Endpoint:</span> <span class="font-mono text-slate-600">https://api.hubnet.com/v1/data/status</span></div>
                                <div><span class="text-slate-400">API Key:</span> <span class="font-mono text-slate-600">sk_live_abc123...</span></div>
                                <div><span class="text-slate-400">Method:</span> <span class="font-bold text-slate-700">POST</span></div>
                                <div><span class="text-slate-400">Timeout:</span> <span class="text-slate-700">30s</span></div>
                                <div><span class="text-slate-400">Retries:</span> <span class="text-slate-700">3</span></div>
                            </div>
                        </div>
                        <div class="px-4 py-2 bg-slate-50 border-t border-b border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Request Headers</span>
                        </div>
                        <div class="px-4 py-3 bg-slate-900">
<pre class="font-mono text-[11px] text-emerald-400 whitespace-pre-wrap m-0">{\n    "Content-Type": "application/json",\n    "Authorization": "Bearer {api_key}"\n}</pre>
                        </div>
                        <div class="px-4 py-2 bg-slate-50 border-t border-b border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Request Body Template</span>
                        </div>
                        <div class="px-4 py-3 bg-slate-900">
<pre class="font-mono text-[11px] text-emerald-400 whitespace-pre-wrap m-0">{\n    "phone": "{phone}",\n    "network": "{network}",\n    "package": "{package}",\n    "amount": "{amount}",\n    "payment_method": "{payment_method}",\n    "order_id": "{order_id}",\n    "reference": "{reference}"\n}</pre>
                        </div>
                        <div class="px-4 py-2 bg-slate-50 border-t border-b border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Response Field Mapping</span>
                        </div>
                        <div class="px-4 py-3 space-y-1 text-xs">
                            <div><span class="text-slate-400">Success Field:</span> <code class="text-indigo-600">success</code></div>
                            <div><span class="text-slate-400">Data Field:</span> <code class="text-indigo-600">data</code></div>
                            <div><span class="text-slate-400">Error Field:</span> <code class="text-indigo-600">error</code></div>
                        </div>
                        <div class="px-4 py-2 bg-slate-50 border-t border-b border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">What Gets Sent at Runtime</span>
                        </div>
                        <div class="px-4 py-3 bg-slate-900">
                            <p class="text-[10px] text-slate-400 mb-2">POST https://api.hubnet.com/v1/data/purchase</p>
<pre class="font-mono text-[11px] text-emerald-400 whitespace-pre-wrap m-0">Headers:\n  Content-Type: application/json\n  Authorization: Bearer sk_live_abc123...\n\nBody:\n{\n    "phone": "233501234567",\n    "network": "MTN",\n    "package": "1024",\n    "amount": 15,\n    "payment_method": "wallet",\n    "order_id": "ORD-2026-0456",\n    "reference": "REF-ABC123XYZ"\n}</pre>
                        </div>
                    </div>
                </div>

                {{-- Common Error Codes --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 text-[10px] font-black flex items-center justify-center">5</span>
                        Common Error Codes & Solutions
                    </h4>
                    <div class="border border-slate-100 rounded-xl overflow-hidden divide-y divide-slate-50">

                        {{-- Critical --}}
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-md uppercase">Critical</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">cURL Error 6 — Could not resolve host</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The endpoint URL domain cannot be resolved by DNS.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Verify the endpoint URL is correct and uses a valid domain. Check for typos.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-md uppercase">Critical</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">cURL Error 7 — Failed to connect to host</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The server refused the connection or is unreachable.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Ensure the API server is online. Check if the port number is correct. Whitelist your server IP if the provider uses IP restrictions.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-md uppercase">Critical</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">cURL Error 28 — Operation timed out</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The API did not respond within the configured timeout period.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Increase the <strong>Timeout</strong> setting (e.g. 30s → 60s). Contact the API provider if their server is slow.</p>
                            </div>
                        </div>

                        {{-- Error --}}
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-md uppercase">Error</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">HTTP 401 — Unauthorized</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The API key or secret is invalid, expired, or missing.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Regenerate your API key from the provider dashboard. Update the key in this configuration. Ensure the <code>{api_key}</code> placeholder is used in headers if required.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-md uppercase">Error</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">HTTP 403 — Forbidden</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">Your server IP is not whitelisted or the API key lacks required permissions.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Request IP whitelisting from the API provider. Verify the API key has data purchase permissions.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-md uppercase">Error</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">HTTP 404 — Not Found</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The endpoint URL path is incorrect.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Verify the full endpoint URL with the API provider's documentation. Ensure no trailing slashes or missing path segments.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-md uppercase">Error</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">HTTP 422 — Unprocessable Entity</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The request body is missing required fields or has invalid data format.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Check the API provider's required fields. Verify your <strong>Request Body Template</strong> includes all mandatory placeholders. Ensure phone numbers include country code.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-md uppercase">Error</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">HTTP 429 — Too Many Requests</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">You've exceeded the API rate limit.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Increase the <strong>Retry Attempts</strong> delay. Contact the provider to raise your rate limit if needed.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-md uppercase">Error</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">HTTP 500 — Internal Server Error</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The API provider's server encountered an internal error.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Retry the request. If persistent, contact the API provider's support team.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-md uppercase">Error</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">HTTP 502 / 503 — Bad Gateway / Service Unavailable</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The API provider's server is down or undergoing maintenance.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Wait and retry. Check the provider's status page. The system will automatically retry based on your retry settings.</p>
                            </div>
                        </div>

                        {{-- Warning --}}
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-yellow-100 text-yellow-700 text-[10px] font-bold rounded-md uppercase">Warning</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">Response: "success: false" with error message</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The API returned a valid response but the order was rejected.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Read the error message in the <code>error</code> field. Common reasons: insufficient provider balance, invalid phone number, invalid package ID, or duplicate order reference.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-yellow-100 text-yellow-700 text-[10px] font-bold rounded-md uppercase">Warning</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">Response success field not recognized</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The system can't determine if the order succeeded because the <strong>Success Field</strong> name doesn't match the API response.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Check the raw API response and update the <strong>Response Field Mapping</strong>. Common alternatives: <code>status</code>, <code>result</code>, <code>ok</code>, <code>successful</code>.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-yellow-100 text-yellow-700 text-[10px] font-bold rounded-md uppercase">Warning</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">Request headers/body template validation failed</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The JSON in <strong>Request Headers</strong> or <strong>Request Body Template</strong> is malformed.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: Use a JSON validator (jsonlint.com) to check syntax. Ensure all braces, quotes, and commas are correct. No trailing commas allowed in JSON.</p>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-md uppercase">Info</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">Test connection succeeds but real orders fail</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The test uses dummy data. Real orders may fail due to actual account constraints.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Ensure the provider account has sufficient balance. Verify package IDs match the provider's catalog. Check phone number format (must include country code).</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 px-4 py-3">
                            <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-md uppercase">Info</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700">Order stuck in "processing" status</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">The API was called but the response hasn't confirmed success or failure yet.</p>
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Check the <strong>Status Check Endpoint</strong> configuration. The system will poll this endpoint to determine the final order status. Ensure it's correctly configured.</p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Troubleshooting --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 text-[10px] font-black flex items-center justify-center">6</span>
                        General Troubleshooting
                    </h4>
                    <div class="space-y-2">
                        <div class="p-3 bg-white border border-slate-100 rounded-xl">
                            <p class="text-xs font-bold text-slate-700 mb-1">Test connection fails with "cURL Error"</p>
                            <p class="text-[11px] text-slate-500">The endpoint URL may be incorrect, the server may be down, or there's a firewall blocking the request. Verify the URL is accessible and uses HTTPS.</p>
                        </div>
                        <div class="p-3 bg-white border border-slate-100 rounded-xl">
                            <p class="text-xs font-bold text-slate-700 mb-1">Test passes but orders fail</p>
                            <p class="text-[11px] text-slate-500">The test uses dummy data. Real orders may fail due to invalid phone numbers, insufficient provider balance, or package ID mismatches. Check the API provider's docs for required field formats.</p>
                        </div>
                        <div class="p-3 bg-white border border-slate-100 rounded-xl">
                            <p class="text-xs font-bold text-slate-700 mb-1">Response shows "success: false"</p>
                            <p class="text-[11px] text-slate-500">The <strong>Success Field</strong> name may not match your provider's response. Check the raw response body and update the field mapping. Some providers use <code>status</code>, <code>result</code>, or <code>ok</code> instead of <code>success</code>.</p>
                        </div>
                        <div class="p-3 bg-white border border-slate-100 rounded-xl">
                            <p class="text-xs font-bold text-slate-700 mb-1">Orders timeout frequently</p>
                            <p class="text-[11px] text-slate-500">Increase the <strong>Timeout</strong> value (e.g. from 30s to 60s) and check if the provider's API is experiencing high latency.</p>
                        </div>
                        <div class="p-3 bg-white border border-slate-100 rounded-xl">
                            <p class="text-xs font-bold text-slate-700 mb-1">"Validation failed" when saving</p>
                            <p class="text-[11px] text-slate-500">The JSON in Headers or Body Template is malformed. Use a JSON validator (like jsonlint.com) to check your JSON syntax. Ensure all braces, quotes, and commas are correct.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- Add/Edit Modal --}}
<div id="apiModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto animate-fade-in">
            <form id="apiForm" method="POST" action="{{ route('admin.api-config.store') }}" onsubmit="return validateNetwork()">
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
                        <input type="text" name="api_name" id="m_name" required placeholder="e.g. Hubnet API, GigBundles" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>

                    {{-- Endpoint --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">API Endpoint <span class="text-red-400">*</span></label>
                        <input type="url" name="api_endpoint" id="m_endpoint" required placeholder="https://api.provider.com/v1/data" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>

                    {{-- Status Endpoint --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status Check Endpoint <span class="text-slate-300 font-normal">(optional)</span></label>
                        <input type="url" name="status_endpoint" id="m_status_endpoint" placeholder="Leave empty to use main endpoint" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                    </div>

                    {{-- Credentials --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">API Key <span class="text-red-400">*</span></label>
                            <input type="text" name="api_key" id="m_api_key" required placeholder="API Key" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">API Secret</label>
                            <input type="text" name="api_secret" id="m_api_secret" placeholder="Optional" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-mono focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition w-full">
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
    function openDocs() {
        var content = document.getElementById('apiDocsContent');
        var chevron = document.getElementById('docsChevron');
        var badge = document.getElementById('docsBadge');
        if (content && content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
            badge.classList.remove('hidden');
            document.getElementById('apiDocsSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    if (window.location.hash === '#docs') { openDocs(); }
    window.addEventListener('hashchange', function() { if (window.location.hash === '#docs') openDocs(); });

    function toggleDocs() {
        const content = document.getElementById('apiDocsContent');
        const chevron = document.getElementById('docsChevron');
        const badge = document.getElementById('docsBadge');
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
            badge.classList.remove('hidden');
        } else {
            content.classList.add('hidden');
            chevron.style.transform = '';
            badge.classList.add('hidden');
        }
    }

    function openAddModal() {
        document.getElementById('m_title').textContent = 'Add Network API';
        document.getElementById('m_submit').textContent = 'Save Configuration';
        document.getElementById('apiForm').reset();
        document.getElementById('apiForm').action = '{{ route("admin.api-config.store") }}';
        document.getElementById('m_network').value = '';
        document.getElementById('m_active').checked = true;
        document.getElementById('networkSelector').style.display = 'block';
        document.querySelectorAll('.network-option').forEach(b => { b.classList.remove('border-[#2563EB]','bg-blue-50'); b.classList.add('border-slate-200'); });
        document.getElementById('m_headers').value = '{\n    "Content-Type": "application/json",\n    "Authorization": "Bearer {api_key}"\n}';
        document.getElementById('m_template').value = '{\n    "phone": "{phone}",\n    "network": "{network}",\n    "package": "{package}",\n    "amount": "{amount}",\n    "payment_method": "{payment_method}",\n    "order_id": "{order_id}",\n    "reference": "{reference}"\n}';
        document.getElementById('m_success').value = 'success';
        document.getElementById('m_data_field').value = 'data';
        document.getElementById('m_error').value = 'error';
        document.getElementById('m_timeout').value = '30';
        document.getElementById('m_retries').value = '3';
        document.getElementById('apiModal').classList.remove('hidden');
    }

    function editConfig(btn) {
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

    function closeModal() { document.getElementById('apiModal').classList.add('hidden'); }

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

    function deleteConfig(id, network) {
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
