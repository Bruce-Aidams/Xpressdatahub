<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    {{-- SEO Meta Tags --}}
    <title>@yield('title', 'Xpressdatahub | Fast, Reliable Data & Airtime Vending')</title>
    <meta name="description" content="@yield('description', 'Xpressdatahub is your trusted platform for instant, affordable data bundles and airtime top-ups. Fast, secure, and reliable service.')">
    <meta name="keywords" content="data vending, cheap data, airtime topup, Xpressdatahub, data bundles, ghana data, affordable airtime">
    <link rel="canonical" href="{{ url()->current() }}">
    
    {{-- Open Graph Meta Tags --}}
    <meta property="og:title" content="@yield('title', 'Xpressdatahub | Data & Airtime Vending')">
    <meta property="og:description" content="@yield('description', 'Get affordable and reliable data bundles and airtime instantly on Xpressdatahub.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('logo.jpg') }}">
    
    {{-- Twitter Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Xpressdatahub')">
    <meta name="twitter:description" content="@yield('description', 'Affordable data bundles and airtime.')">
    <meta name="twitter:image" content="{{ asset('logo.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        body { background-color: #f8fafc; color: #1e293b; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    @yield('body')
    @stack('scripts')
</body>
</html>
