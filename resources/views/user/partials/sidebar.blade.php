@php
    $currentRoute = request()->route()->getName();
    $isGuest = ($userRole ?? '') === 'guest';
@endphp

<aside
    id="sidebar"
    class="fixed left-0 top-0 z-40 h-screen w-64 bg-white border-r border-slate-100/80 transition-all duration-300 -translate-x-full lg:translate-x-0 shadow-sm lg:shadow-none"
>
    <div class="flex h-full flex-col">
        {{-- Logo --}}
        <div class="flex h-16 items-center gap-3 px-5 border-b border-slate-100/80">
            <a href="{{ route('user.buy-data') }}" class="flex items-center gap-3 group">
                <div class="h-9 w-9 rounded-xl overflow-hidden shrink-0 shadow-sm ring-2 ring-slate-100 group-hover:ring-[#EA580C]/30 transition-all duration-300">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Xpressdatahub" class="w-full h-full object-cover">
                </div>
                <div class="leading-tight">
                    <p class="text-[13px] font-bold text-slate-800 group-hover:text-[#EA580C] transition-colors duration-200">Xpressdatahub</p>
                    @if($isGuest)
                    <p class="text-[10px] text-amber-500 font-semibold uppercase tracking-wider">Guest Mode</p>
                    @else
                    <p class="text-[10px] text-[#EA580C] font-semibold uppercase tracking-wider">Vendor Portal</p>
                    @endif
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <div class="flex-1 overflow-y-auto px-3 py-4 sidebar-scroll">
            <div class="space-y-1">

                @if(!$isGuest)
                {{-- Dashboard --}}
                <a href="{{ route('user.dashboard') }}"
                   class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.dashboard') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.dashboard') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                        <x-heroicon-o-squares-2x2 class="w-4 h-4 {{ request()->routeIs('user.dashboard') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                    </div>
                    <span>Dashboard</span>
                </a>
                @endif

                {{-- Buy Data --}}
                <a href="{{ route('user.buy-data') }}"
                   class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.buy-data*') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.buy-data*') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                        <x-heroicon-o-shopping-cart class="w-4 h-4 {{ request()->routeIs('user.buy-data*') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                    </div>
                    <span>Buy Data</span>
                </a>



                @if(!$isGuest)
                {{-- Wallet --}}
                <a href="{{ route('user.wallet.topup') }}"
                   class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.wallet.*') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.wallet.*') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                        <x-heroicon-o-wallet class="w-4 h-4 {{ request()->routeIs('user.wallet.*') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                    </div>
                    <span>Top Up Wallet</span>
                </a>

                {{-- Orders --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Orders</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('user.orders.today') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.orders.today') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.orders.today') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-clock class="w-4 h-4 {{ request()->routeIs('user.orders.today') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                            </div>
                            <span>Today's Orders</span>
                        </a>
                        <a href="{{ route('user.orders') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.orders') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.orders') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-queue-list class="w-4 h-4 {{ request()->routeIs('user.orders') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                            </div>
                            <span>All Orders</span>
                        </a>
                    </div>
                </div>

                {{-- Shop --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Shop</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('user.shop.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.shop.index') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.shop.index') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-building-storefront class="w-4 h-4 {{ request()->routeIs('user.shop.index') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                            </div>
                            <span>My Shop</span>
                        </a>
                        <a href="{{ route('user.shop-profits.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.shop-profits.*') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.shop-profits.*') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-banknotes class="w-4 h-4 {{ request()->routeIs('user.shop-profits.*') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                            </div>
                            <span>Shop Profit</span>
                        </a>
                    </div>
                </div>

                {{-- Referrals --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Referrals</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('user.referrals.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.referrals.*') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.referrals.*') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-user-group class="w-4 h-4 {{ request()->routeIs('user.referrals.*') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                            </div>
                            <span>Referrals</span>
                        </a>
                    </div>
                </div>

                {{-- Finance --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Finance</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('user.balance-history.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.balance-history.*') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.balance-history.*') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-clock class="w-4 h-4 {{ request()->routeIs('user.balance-history.*') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                            </div>
                            <span>Balance History</span>
                        </a>
                    </div>
                </div>

                {{-- Account --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Account</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('user.notifications.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.notifications.*') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.notifications.*') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-bell class="w-4 h-4 {{ request()->routeIs('user.notifications.*') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                            </div>
                            <span>Notifications</span>
                        </a>
                        <a href="{{ route('user.profile.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.profile.*') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.profile.*') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-user class="w-4 h-4 {{ request()->routeIs('user.profile.*') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                            </div>
                            <span>Profile</span>
                        </a>
                        <a href="{{ route('user.password.change') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.password.*') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.password.*') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-key class="w-4 h-4 {{ request()->routeIs('user.password.*') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                            </div>
                            <span>Password</span>
                        </a>
                        <a href="{{ route('user.api-keys.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('user.api-keys.*') ? 'bg-gradient-to-r from-[#EA580C]/10 to-[#EA580C]/5 text-[#EA580C] shadow-sm shadow-[#EA580C]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('user.api-keys.*') ? 'bg-[#EA580C]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-code-bracket class="w-4 h-4 {{ request()->routeIs('user.api-keys.*') ? 'text-[#EA580C]' : 'text-slate-400' }}" />
                            </div>
                            <span>API Keys</span>
                        </a>
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- Footer --}}
        <div class="border-t border-slate-100/80 px-3 py-3 space-y-1">
            <div class="flex items-center gap-3 rounded-xl px-3 py-2.5">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#EA580C] to-[#FB923C] flex items-center justify-center text-white text-[11px] font-bold shadow-sm">
                    {{ strtoupper(substr($currentUser->username ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-slate-700 truncate">{{ $currentUser->username ?? '' }}</p>
                    @if($isGuest)
                    <p class="text-[10px] text-slate-400 font-medium">Guest session</p>
                    @else
                    <p class="text-[10px] text-[#EA580C] font-bold">GH&#8373;{{ number_format($currentUser->balance ?? 0, 2) }}</p>
                    @endif
                </div>
            </div>
            @if($isGuest)
            <a href="{{ route('user.guest.logout') }}" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-400 hover:text-red-500 hover:bg-red-50/80 transition-all duration-200">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100/80">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                </div>
                <span>End Guest Session</span>
            </a>
            @else
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-400 hover:text-red-500 hover:bg-red-50/80 transition-all duration-200">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100/80">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                    </div>
                    <span>Sign out</span>
                </button>
            </form>
            @endif
        </div>
    </div>
</aside>
