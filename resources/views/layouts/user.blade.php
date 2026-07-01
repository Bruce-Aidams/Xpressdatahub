<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', 'Dashboard') - Xpressdatahub</title>
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
        .nav-item::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 0; background: #EA580C; border-radius: 0 4px 4px 0; transition: height 0.2s ease; }
        .nav-item.active::before { height: 20px; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.3s ease; }
        .flash-message { animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        #sidebarBackdrop { transition: opacity 0.3s ease; }
        @media (min-width: 1024px) { #sidebarBackdrop { display: none !important; } }
    </style>
</head>
<body class="bg-[#F8F9FC] min-h-screen">

    {{-- Sidebar Toggle Mobile --}}
    <button onclick="openSidebar()" class="lg:hidden fixed top-3 left-3 z-[60] bg-white text-slate-600 p-2.5 rounded-xl shadow-lg border border-slate-200/60 hover:bg-slate-50 transition-colors" id="sidebarToggle">
        <x-heroicon-o-bars-3 class="w-5 h-5" />
    </button>

    {{-- Sidebar Backdrop (mobile) --}}
    <div id="sidebarBackdrop" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40 hidden lg:hidden" onclick="closeSidebar()"></div>

    {{-- Sidebar --}}
    @include('user.partials.sidebar')

    {{-- Main Content Area --}}
    <div class="lg:ml-64 min-h-screen flex flex-col" id="mainContent">
        {{-- Top Bar Header --}}
        <div class="bg-white/80 backdrop-blur-xl border-b border-slate-100/80 px-4 sm:px-6 py-3 flex items-center justify-between sticky top-0 z-30">
            {{-- Left: Page Title --}}
            <div class="flex items-center gap-3 min-w-0 flex-1 ml-10 lg:ml-0">
                <div class="min-w-0">
                    <h1 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight truncate">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 font-medium truncate hidden sm:block">@yield('page-description')</p>
                </div>
            </div>

            {{-- Right: Actions --}}
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                {{-- Balance pill --}}
                @if(($userRole ?? '') !== 'guest')
                <div class="hidden sm:flex items-center gap-2 bg-[#EA580C]/5 border border-[#EA580C]/15 rounded-xl px-3 py-1.5">
                    <x-heroicon-o-wallet class="text-[#EA580C] w-4 h-4" />
                    <span class="text-[13px] font-bold text-[#EA580C] tabular-nums">GH&#8373;{{ number_format($currentUser->balance ?? 0, 2) }}</span>
                </div>

                {{-- Top up button --}}
                <a
                    href="{{ route('user.wallet.topup') }}"
                    class="hidden sm:inline-flex items-center gap-1.5 bg-[#EA580C] hover:bg-[#C2410C] text-white px-3.5 py-2 rounded-xl text-[13px] font-semibold shadow-sm shadow-orange-500/10 transition-all duration-200"
                >
                    <x-heroicon-o-plus-circle class="w-4 h-4" />
                    <span>Top up</span>
                </a>
                @else
                <span class="hidden sm:inline-flex items-center gap-1.5 bg-slate-100 text-slate-500 px-3 py-1.5 rounded-xl text-[11px] font-bold">
                    <x-heroicon-o-user-circle class="w-4 h-4" /> Guest Mode
                </span>
                @endif

                {{-- User Menu --}}
                <div class="relative" id="userMenu">
                    <button onclick="toggleUserMenu()" class="flex items-center gap-2 pl-1 pr-2 sm:pr-3 py-1 rounded-xl bg-slate-50/80 border border-slate-200/50 hover:border-slate-300/60 hover:bg-white transition-all duration-200">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-[#EA580C] to-[#FB923C] flex items-center justify-center text-white text-[11px] font-bold shadow-sm">
                            {{ strtoupper(substr($currentUser->username ?? 'U', 0, 1)) }}
                        </div>
                        <span class="text-xs font-semibold text-slate-600 hidden sm:block">{{ $currentUser->username ?? '' }}</span>
                        <span id="userMenuChevron"><x-heroicon-o-chevron-down class="h-2.5 w-2.5 text-slate-400 transition-transform duration-200 hidden sm:block" /></span>
                    </button>

                    <div id="userDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-xl border border-slate-100 shadow-xl shadow-slate-200/50 py-2 z-50 hidden opacity-0 transform scale-95 -translate-y-1 transition-all duration-150">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="text-sm font-bold text-slate-800">{{ $currentUser->username ?? '' }}</p>
                            @if(($userRole ?? '') !== 'guest')
                            <p class="text-[11px] text-[#EA580C] font-medium">GH&#8373;{{ number_format($currentUser->balance ?? 0, 2) }} balance</p>
                            @else
                            <p class="text-[11px] text-slate-400 font-medium">Guest session</p>
                            @endif
                        </div>
                        @if(($userRole ?? '') !== 'guest')
                        <a href="{{ route('user.profile.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                            <x-heroicon-o-user-circle class="text-slate-400 w-4 h-4" /> My Profile
                        </a>
                        <a href="{{ route('user.password.change') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                            <x-heroicon-o-key class="text-slate-400 w-4 h-4" /> Password
                        </a>
                        <a href="{{ route('user.api-keys.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                            <x-heroicon-o-code-bracket class="text-slate-400 w-4 h-4" /> API Keys
                        </a>
                        @endif
                        <div class="border-t border-slate-100 mt-1 pt-1">
                            @if(($userRole ?? '') !== 'guest')
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" /> Sign out
                                </button>
                            </form>
                            @else
                            <a href="{{ route('user.guest.logout') }}" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" /> End Guest Session
                            </a>
                            @endif
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
            var sidebar = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.remove('-translate-x-full');
            if (backdrop) {
                backdrop.classList.remove('hidden');
                requestAnimationFrame(function() { backdrop.style.opacity = '1'; });
            }
        }

        function closeSidebar() {
            var sidebar = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.add('-translate-x-full');
            if (backdrop) {
                backdrop.style.opacity = '0';
                setTimeout(function() { backdrop.classList.add('hidden'); }, 300);
            }
        }

        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('-translate-x-full')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        }

        function toggleUserMenu() {
            var dropdown = document.getElementById('userDropdown');
            var chevron = document.getElementById('userMenuChevron');
            var isOpen = !dropdown.classList.contains('hidden');

            if (isOpen) {
                dropdown.classList.add('hidden');
                dropdown.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
                dropdown.classList.add('opacity-0', 'scale-95', '-translate-y-1');
                if (chevron) chevron.classList.remove('rotate-180');
            } else {
                dropdown.classList.remove('hidden');
                requestAnimationFrame(function() {
                    dropdown.classList.remove('opacity-0', 'scale-95', '-translate-y-1');
                    dropdown.classList.add('opacity-100', 'scale-100', 'translate-y-0');
                });
                if (chevron) chevron.classList.add('rotate-180');
            }
        }

        document.addEventListener('click', function(e) {
            var menu = document.getElementById('userMenu');
            var dropdown = document.getElementById('userDropdown');
            if (menu && dropdown && !menu.contains(e.target)) {
                dropdown.classList.add('hidden');
                dropdown.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
                dropdown.classList.add('opacity-0', 'scale-95', '-translate-y-1');
                var chevron = document.getElementById('userMenuChevron');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var dropdown = document.getElementById('userDropdown');
                if (dropdown) {
                    dropdown.classList.add('hidden');
                    dropdown.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
                    dropdown.classList.add('opacity-0', 'scale-95', '-translate-y-1');
                    var chevron = document.getElementById('userMenuChevron');
                    if (chevron) chevron.classList.remove('rotate-180');
                }
                closeSidebar();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
