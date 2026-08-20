<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', 'Admin Dashboard') - Xpressdatahub</title>
    <meta name="description" content="@yield('description', 'Xpressdatahub Admin Panel')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #f1f5f9; border-radius: 10px; }
        .nav-item { position: relative; }
        .nav-item::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 0; background: #2563EB; border-radius: 0 4px 4px 0; transition: height 0.2s ease; }
        .nav-item.active::before { height: 20px; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.3s ease; }
        .stat-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px -5px rgba(0,0,0,0.1), 0 4px 10px -5px rgba(0,0,0,0.04); }
        .table-row { transition: all 0.15s ease; }
        .table-row:hover { background-color: rgba(255, 122, 0, 0.03); }
        .flash-message { animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        #sidebarBackdrop { transition: opacity 0.3s ease; }
        @media (min-width: 1024px) { #sidebarBackdrop { display: none !important; } }
    </style>
</head>
<body class="bg-[#F8F9FC] min-h-screen" autocomplete="off">
    {{-- Sidebar Toggle Mobile --}}
    <button onclick="openSidebar()" class="lg:hidden fixed top-3 left-3 z-[60] bg-white text-slate-600 p-2.5 rounded-xl shadow-lg border border-slate-200/60 hover:bg-slate-50 transition-colors" id="sidebarToggle">
        <x-heroicon-o-bars-3 class="w-5 h-5" />
    </button>

    {{-- Sidebar Backdrop (mobile) --}}
    <div id="sidebarBackdrop" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40 hidden lg:hidden" onclick="closeSidebar()"></div>

    {{-- Sidebar --}}
    @include('admin.partials.sidebar')

    {{-- Main Content Area --}}
    <div class="lg:ml-64 min-h-screen flex flex-col" id="mainContent">
        {{-- Top Bar Header --}}
        <div class="bg-white/80 backdrop-blur-xl border-b border-slate-100/80 px-4 sm:px-6 py-3 flex items-center justify-between sticky top-0 z-30">
            {{-- Left: Page Title --}}
            <div class="flex items-center gap-3 min-w-0 flex-1 ml-10 lg:ml-0">
                <div class="min-w-0">
                    <h1 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight truncate">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 font-medium truncate hidden sm:block">@yield('page-description', 'Xpressdatahub Admin Panel')</p>
                </div>
            </div>

            {{-- Right: Actions --}}
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                {{-- Date --}}
                <div class="hidden md:flex items-center gap-2 bg-slate-50/80 border border-slate-200/50 rounded-xl px-3.5 py-2 text-xs text-slate-500 font-medium">
                    <x-heroicon-o-calendar class="text-slate-400 w-4 h-4" />
                    <span>{{ now()->format('D, M d, Y') }}</span>
                </div>

                {{-- Divider --}}
                <div class="hidden md:block w-px h-6 bg-slate-200/60"></div>

                {{-- Notifications --}}
                <a href="{{ route('admin.notifications.index') }}" class="relative w-9 h-9 rounded-xl bg-slate-50/80 border border-slate-200/50 flex items-center justify-center text-slate-400 hover:text-[#2563EB] hover:bg-[#2563EB]/5 hover:border-[#2563EB]/20 transition-all duration-200">
                    <x-heroicon-o-bell class="w-5 h-5" />
                </a>

                {{-- User Menu --}}
                <div class="relative" id="userMenu">
                    <button onclick="toggleUserMenu()" class="flex items-center gap-2 pl-1 pr-2 sm:pr-3 py-1 rounded-xl bg-slate-50/80 border border-slate-200/50 hover:border-slate-300/60 hover:bg-white transition-all duration-200">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-[#2563EB] to-[#60A5FA] flex items-center justify-center text-white text-[11px] font-bold shadow-sm">
                            {{ strtoupper(substr(session('admin_username', 'A'), 0, 1)) }}
                        </div>
                        <span class="text-xs font-semibold text-slate-600 hidden sm:block">{{ session('admin_username', 'Admin') }}</span>
                        <span id="userMenuChevron"><x-heroicon-o-chevron-down class="h-2.5 w-2.5 text-slate-400 transition-transform duration-200 hidden sm:block" /></span>
                    </button>

                    <div id="userDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-xl border border-slate-100 shadow-xl shadow-slate-200/50 py-2 z-50 hidden opacity-0 transform scale-95 -translate-y-1 transition-all duration-150">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="text-sm font-bold text-slate-800">{{ session('admin_username', 'Admin') }}</p>
                            <p class="text-[11px] text-slate-400">{{ session('admin_role', 'admin') }}</p>
                        </div>
                        <a href="{{ route('admin.profile.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                            <x-heroicon-o-user-circle class="text-slate-400 w-4 h-4" /> My Profile
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                            <x-heroicon-o-squares-2x2 class="text-slate-400 w-4 h-4" /> Dashboard
                        </a>
                        <div class="border-t border-slate-100 mt-1 pt-1">
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" /> Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        <div class="px-4 sm:px-6 pt-3 sm:pt-4">
            @if(session('success'))
                <div class="flash-message mb-4 p-3 sm:p-3.5 bg-emerald-50 border border-emerald-200/60 rounded-xl flex items-center gap-3 text-emerald-700 text-sm shadow-sm">
                    <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                        <x-heroicon-o-check class="text-emerald-600 w-4 h-4" />
                    </div>
                    <span class="flex-1 font-medium text-[13px]">{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600 transition-colors"><x-heroicon-o-x-mark class="w-4 h-4" /></button>
                </div>
            @endif
            @if(session('error'))
                <div class="flash-message mb-4 p-3 sm:p-3.5 bg-red-50 border border-red-200/60 rounded-xl flex items-center gap-3 text-red-700 text-sm shadow-sm">
                    <div class="w-7 h-7 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <x-heroicon-o-exclamation-triangle class="text-red-600 w-4 h-4" />
                    </div>
                    <span class="flex-1 font-medium text-[13px]">{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 transition-colors"><x-heroicon-o-x-mark class="w-4 h-4" /></button>
                </div>
            @endif
            @if($errors->any())
                <div class="flash-message mb-4 p-3 sm:p-3.5 bg-red-50 border border-red-200/60 rounded-xl text-red-700 text-sm shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-7 h-7 rounded-full bg-red-100 flex items-center justify-center shrink-0">
<x-heroicon-o-exclamation-triangle class="text-red-600 w-4 h-4" />
                        </div>
                        <span class="font-bold text-[13px]">Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 ml-9 text-[13px]">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Page Content --}}
        <div class="p-3 sm:p-4 lg:p-6 flex-1">
            @yield('content')
        </div>
    </div>

    <script>
        function openSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.remove('-translate-x-full');
            if (backdrop) {
                backdrop.classList.remove('hidden');
                requestAnimationFrame(() => { backdrop.style.opacity = '1'; });
            }
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.add('-translate-x-full');
            if (backdrop) {
                backdrop.style.opacity = '0';
                setTimeout(() => { backdrop.classList.add('hidden'); }, 300);
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('-translate-x-full')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        }

        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            const chevron = document.getElementById('userMenuChevron');
            const isOpen = !dropdown.classList.contains('hidden');

            if (isOpen) {
                dropdown.classList.add('hidden');
                dropdown.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
                dropdown.classList.add('opacity-0', 'scale-95', '-translate-y-1');
                if (chevron) chevron.classList.remove('rotate-180');
            } else {
                dropdown.classList.remove('hidden');
                requestAnimationFrame(() => {
                    dropdown.classList.remove('opacity-0', 'scale-95', '-translate-y-1');
                    dropdown.classList.add('opacity-100', 'scale-100', 'translate-y-0');
                });
                if (chevron) chevron.classList.add('rotate-180');
            }
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('userMenu');
            const dropdown = document.getElementById('userDropdown');
            if (menu && dropdown && !menu.contains(e.target)) {
                dropdown.classList.add('hidden');
                dropdown.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
                dropdown.classList.add('opacity-0', 'scale-95', '-translate-y-1');
                const chevron = document.getElementById('userMenuChevron');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown) {
                    dropdown.classList.add('hidden');
                    dropdown.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
                    dropdown.classList.add('opacity-0', 'scale-95', '-translate-y-1');
                    const chevron = document.getElementById('userMenuChevron');
                    if (chevron) chevron.classList.remove('rotate-180');
                }
                closeSidebar();
            }
        });
        // Reset all forms on every page load (including back/forward cache) so no stale data is shown
        window.addEventListener('pageshow', function(e) {
            document.querySelectorAll('form:not([data-keep-values])').forEach(function(form) {
                // Skip filter/search forms (GET method) – they reflect URL params intentionally
                if (form.method && form.method.toUpperCase() === 'GET') return;
                form.reset();
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
