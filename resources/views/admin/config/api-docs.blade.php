@extends('layouts.admin')
@section('page-title', 'API Documentation')
@section('page-description', 'Developer API reference and outbound API setup guide')
@section('content')
<div class="max-w-6xl">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">API Documentation &amp; Setup Guide</h1>
        <p class="text-sm text-slate-400 mt-1">Step-by-step guide to configure network APIs with examples</p>
    </div>

    {{-- Tabs Navigation --}}
    <div class="flex border-b border-slate-100 bg-slate-50/50 px-4 py-2 gap-2 rounded-t-2xl bg-white border border-slate-100/80 shadow-sm">
        <button type="button" onclick="switchDocTab('dev-api', this)" id="tab-btn-dev-api" class="doc-tab-btn px-4 py-2 text-xs font-bold rounded-xl bg-[#2563EB] text-white">
            Developer API Reference
        </button>
        <button type="button" onclick="switchDocTab('outbound-api', this)" id="tab-btn-outbound-api" class="doc-tab-btn px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100">
            Outbound API Setup Guide
        </button>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-b-2xl shadow-sm overflow-hidden mb-6">

        {{-- Tab 1: Developer API Content --}}
        <div id="dev-api-content" class="doc-tab-content px-6 pb-6 space-y-6 pt-5">
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 text-[10px] font-black flex items-center justify-center">1</span>
                    Authentication
                </h4>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Incoming requests to Xpressdatahub must authenticate by passing your API Key in the <code class="bg-slate-100 px-1 rounded font-mono">X-API-Key</code> header, as a query parameter <code class="bg-slate-100 px-1 rounded font-mono">?api_key=</code>, or in the request body.
                </p>
            </div>

            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 text-[10px] font-black flex items-center justify-center">2</span>
                    Endpoints
                </h4>
                <div class="space-y-4">
                    {{-- Balance --}}
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded">GET</span>
                                <span class="font-mono text-xs text-slate-700">/api/v1/wallet/balance</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-medium">Check Wallet Balance</span>
                        </div>
                        <div class="p-3 bg-slate-900 font-mono text-[10px] text-emerald-400 overflow-x-auto">
<pre class="m-0">// Response (200 OK)
{
    "success": true,
    "balance": 150.75,
    "currency": "GH₵"
}</pre>
                        </div>
                    </div>

                    {{-- Packages --}}
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded">GET</span>
                                <span class="font-mono text-xs text-slate-700">/api/v1/packages</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-medium">List Available Packages</span>
                        </div>
                        <div class="p-3 bg-slate-900 font-mono text-[10px] text-emerald-400 overflow-x-auto">
<pre class="m-0">// Response (200 OK)
{
    "success": true,
    "packages": [
        {
            "id": 1,
            "network_type": "MTN",
            "package_size": "1.5GB",
            "selling_price": 10.00,
            "cost": 8.50
        }
    ]
}</pre>
                        </div>
                    </div>

                    {{-- Create Order --}}
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-bold rounded">POST</span>
                                <span class="font-mono text-xs text-slate-700">/api/v1/orders</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-medium">Create Order</span>
                        </div>
                        <div class="p-3 border-b border-slate-100 text-xs text-slate-600 space-y-1 bg-white">
                            <p><strong>Request Body:</strong></p>
                            <pre class="bg-slate-50 p-2 rounded font-mono text-[10px]">{ "phone_number": "0241234567", "network_type": "MTN", "package_size": "1.5GB" }</pre>
                        </div>
                        <div class="p-3 bg-slate-900 font-mono text-[10px] text-emerald-400 overflow-x-auto">
<pre class="m-0">// Response (200 OK - Processing)
{
    "success": true,
    "order_id": 482,
    "order_reference": "ORD-5F2A8D9B-1715892",
    "status": "processing",
    "message": "Order submitted successfully and is now being processed"
}</pre>
                        </div>
                    </div>

                    {{-- Status Check --}}
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded">GET</span>
                                <span class="font-mono text-xs text-slate-700">/api/v1/orders/status/{reference}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-medium">Check Order Status</span>
                        </div>
                        <div class="p-3 bg-slate-900 font-mono text-[10px] text-emerald-400 overflow-x-auto">
<pre class="m-0">// Response (200 OK)
{
    "success": true,
    "order": {
        "id": 482,
        "status": "processing",
        "order_reference": "ORD-5F2A8D9B-1715892"
    }
}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 text-[10px] font-black flex items-center justify-center">3</span>
                    HTTP Status &amp; Error Codes
                </h4>
                <div class="border border-slate-100 rounded-xl overflow-hidden divide-y divide-slate-100 text-xs">
                    <div class="p-3 flex items-start gap-4">
                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded">401</span>
                        <div>
                            <p class="font-bold text-slate-700">Unauthorized</p>
                            <p class="text-slate-500 mt-0.5">Missing or invalid API key (<code class="bg-slate-50 px-1 rounded font-mono">"API key is required"</code> or <code class="bg-slate-50 px-1 rounded font-mono">"Invalid API key"</code>)</p>
                        </div>
                    </div>
                    <div class="p-3 flex items-start gap-4">
                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded">403</span>
                        <div>
                            <p class="font-bold text-slate-700">Forbidden</p>
                            <p class="text-slate-500 mt-0.5">API key is inactive/expired, or agent account is inactive.</p>
                        </div>
                    </div>
                    <div class="p-3 flex items-start gap-4">
                        <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded">402</span>
                        <div>
                            <p class="font-bold text-slate-700">Payment Required</p>
                            <p class="text-slate-500 mt-0.5">Insufficient wallet balance to place this order.</p>
                        </div>
                    </div>
                    <div class="p-3 flex items-start gap-4">
                        <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded">422</span>
                        <div>
                            <p class="font-bold text-slate-700">Unprocessable Entity</p>
                            <p class="text-slate-500 mt-0.5">Validation failed: Invalid Ghana phone number or network/package mismatches.</p>
                        </div>
                    </div>
                    <div class="p-3 flex items-start gap-4">
                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded">429</span>
                        <div>
                            <p class="font-bold text-slate-700">Too Many Requests</p>
                            <p class="text-slate-500 mt-0.5">Rate limit exceeded. Check the <code class="bg-slate-50 px-1 rounded font-mono">Retry-After</code> header response.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 2: Outbound API Content --}}
        <div id="outbound-api-content" class="doc-tab-content px-6 pb-6 space-y-6 pt-5 hidden">
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
                    @foreach([
                        ['1', 'Click "Add Network API"', 'Click the <strong class="text-[#2563EB]">Add Network API</strong> button at the top-right of the page. A modal form will open with all configuration fields.'],
                        ['2', 'Select the Network', 'Choose which network this API is for: <strong>MTN</strong>, <strong>AirtelTigo</strong>, or <strong>Telecel</strong>. This determines which orders are routed to this API. You can only have one active API per network.'],
                        ['3', 'Enter API Name', 'Give the configuration a recognizable name. This is for your reference only.<br><div class="mt-2 p-2 bg-white border border-slate-100 rounded-lg font-mono text-[11px] text-slate-600">Examples: <code class="text-indigo-600">Flutterwave API</code>, <code class="text-indigo-600">Paystack</code>, <code class="text-indigo-600">GooglePay Data API</code></div>'],
                        ['4', 'Enter the API Endpoint URL', 'This is the full URL where data orders will be sent. Must be a valid HTTPS URL.<br><div class="mt-2 p-2 bg-white border border-slate-100 rounded-lg font-mono text-[11px] text-slate-600">Example: <code class="text-indigo-600">https://api.provider.com/v1/data/purchase</code></div><div class="mt-2 text-[11px] text-slate-400">The system will POST order data (phone, network, package, amount) to this URL.</div>'],
                        ['5', 'Status Check Endpoint <span class="text-slate-400 font-normal">(optional)</span>', 'If the API provider has a separate endpoint to check order status, enter it here. If left empty, the system will use the main endpoint for status checks.'],
                        ['6', 'Enter API Credentials', '<strong>API Key</strong> (required): Your authentication key from the provider.<br><strong>API Secret</strong> (optional): If the provider requires a second secret for authentication.<br><div class="mt-2 text-[11px] text-slate-400">These are stored encrypted and never exposed in the admin panel (masked as <code>••••••••xxxxxx</code>).</div>'],
                        ['7', 'Configure Request Settings', '<div><strong>Request Method:</strong> Typically <code class="bg-slate-100 px-1 rounded">POST</code> for data purchases.</div><div><strong>Timeout:</strong> How long (in seconds) to wait for a response. Default: <code class="bg-slate-100 px-1 rounded">30s</code>.</div><div><strong>Retry Attempts:</strong> How many times to retry on failure. Default: <code class="bg-slate-100 px-1 rounded">3</code>.</div>'],
                        ['8', 'Configure Request Headers', 'Enter the HTTP headers as JSON. Use <code class="bg-slate-100 px-1 rounded">{api_key}</code> to dynamically inject the API key.<br><div class="mt-2 p-3 bg-slate-900 rounded-lg font-mono text-[11px] text-emerald-400">{\"Content-Type\": \"application/json\", \"X-API-Key\": \"{api_key}\"}</div>'],
                        ['9', 'Configure Request Body Template', 'Define the JSON body sent to the API. Use placeholders for dynamic values.<br><div class="mt-2 p-3 bg-slate-900 rounded-lg font-mono text-[11px] text-emerald-400">{\"recipient_phone\": \"{phone}\",\"network\": \"{network}\", \"size_gb\": \"{capacity}\"}</div>'],
                        ['10', 'Map Response Fields', 'Tell the system how to read the API\'s response JSON. Defaults: <code class="bg-slate-100 px-1 rounded">success</code>, <code class="bg-slate-100 px-1 rounded">data</code>, <code class="bg-slate-100 px-1 rounded">error</code>.'],
                        ['11', 'Activate &amp; Test', 'Check <strong>"Active"</strong> to enable the API immediately. After saving, click the signal icon to run a test connection.'],
                    ] as [$step, $title, $desc])
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <div class="flex items-center gap-3 px-4 py-3 bg-slate-50">
                            <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white text-[11px] font-bold flex items-center justify-center">{{ $step }}</span>
                            <span class="text-sm font-semibold text-slate-700">{!! $title !!}</span>
                        </div>
                        <div class="px-4 py-3 text-xs text-slate-500 leading-relaxed">{!! $desc !!}</div>
                    </div>
                    @endforeach
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

            {{-- Common Errors --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 text-[10px] font-black flex items-center justify-center">4</span>
                    Common Error Codes &amp; Solutions
                </h4>
                <div class="border border-slate-100 rounded-xl overflow-hidden divide-y divide-slate-50">
                    @foreach([
                        ['Critical', 'cURL Error 6 — Could not resolve host', 'The endpoint URL domain cannot be resolved by DNS.', 'Verify the endpoint URL is correct and uses a valid domain. Check for typos.'],
                        ['Critical', 'cURL Error 7 — Failed to connect to host', 'The server refused the connection or is unreachable.', 'Ensure the API server is online. Check if the port number is correct. Whitelist your server IP if the provider uses IP restrictions.'],
                        ['Critical', 'cURL Error 28 — Operation timed out', 'The API did not respond within the configured timeout.', 'Increase the Timeout setting. Check for server-side performance issues with the provider.'],
                        ['Warning', 'Response: "success: false" with error message', 'The API returned a valid response but the order was rejected.', 'Read the error message in the error field. Common reasons: insufficient provider balance, invalid phone number, invalid package ID, or duplicate order reference.'],
                        ['Warning', 'Response success field not recognized', 'The system can\'t determine if the order succeeded.', 'Check the raw API response and update the Response Field Mapping. Common alternatives: status, result, ok, successful.'],
                        ['Warning', 'Request headers/body template validation failed', 'The JSON in Request Headers or Body Template is malformed.', 'Use a JSON validator (jsonlint.com) to check syntax. Ensure all braces, quotes, and commas are correct.'],
                        ['Info', 'Test connection succeeds but real orders fail', 'The test uses dummy data. Real orders may fail due to actual account constraints.', 'Ensure the provider account has sufficient balance. Verify package IDs match the provider\'s catalog.'],
                        ['Info', 'Order stuck in "processing" status', 'The API was called but the response hasn\'t confirmed success or failure yet.', 'Check the Status Check Endpoint configuration. The system will poll this endpoint to determine the final order status.'],
                    ] as [$severity, $title, $desc, $fix])
                    @php
                        $colors = ['Critical' => 'bg-red-100 text-red-700', 'Warning' => 'bg-yellow-100 text-yellow-700', 'Info' => 'bg-blue-100 text-blue-700'];
                    @endphp
                    <div class="flex items-start gap-4 px-4 py-3">
                        <span class="shrink-0 mt-0.5 px-2 py-0.5 {{ $colors[$severity] }} text-[10px] font-bold rounded-md uppercase">{{ $severity }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-700">{{ $title }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ $desc }}</p>
                            <p class="text-[11px] text-emerald-600 mt-1 font-medium">Fix: {{ $fix }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function switchDocTab(tab, btn) {
    document.querySelectorAll('.doc-tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.doc-tab-btn').forEach(el => {
        el.classList.remove('bg-[#2563EB]', 'text-white');
        el.classList.add('text-slate-600', 'hover:bg-slate-100');
    });
    document.getElementById(tab + '-content').classList.remove('hidden');
    btn.classList.add('bg-[#2563EB]', 'text-white');
    btn.classList.remove('text-slate-600', 'hover:bg-slate-100');
}
</script>
@endpush
@endsection
