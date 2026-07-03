@php
    $currentRoute = request()->route() ? request()->route()->getName() : null;
    $isActive = fn($pattern) => request()->routeIs($pattern) ? 'active' : '';
@endphp

<aside
    id="sidebar"
    class="fixed left-0 top-0 z-40 h-screen w-64 bg-white border-r border-slate-100/80 transition-all duration-300 -translate-x-full lg:translate-x-0 shadow-sm lg:shadow-none"
>
    <div class="flex h-full flex-col">
        {{-- Logo --}}
        <div class="flex h-16 items-center gap-3 px-5 border-b border-slate-100/80">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="h-9 w-9 rounded-xl overflow-hidden shrink-0 shadow-sm ring-2 ring-slate-100 group-hover:ring-[#2563EB]/30 transition-all duration-300">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Xpressdatahub" class="w-full h-full object-cover">
                </div>
                <div class="leading-tight">
                    <p class="text-[13px] font-bold text-slate-800 group-hover:text-[#2563EB] transition-colors duration-200">Xpressdatahub</p>
                    <p class="text-[10px] text-slate-400 font-medium">Admin Panel</p>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <div class="flex-1 overflow-y-auto px-3 py-4 sidebar-scroll">
            <div class="space-y-1">

                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80 group-hover:bg-slate-100' }}">
                        <x-heroicon-o-squares-2x2 class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                    </div>
                    <span>Dashboard</span>
                </a>

                {{-- Orders --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Orders</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('admin.orders.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.orders.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.orders.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-shopping-bag class="w-4 h-4 {{ request()->routeIs('admin.orders.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Orders</span>
                        </a>
                        <a href="{{ route('admin.orders.all') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.orders.all') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.orders.all') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-queue-list class="w-4 h-4 {{ request()->routeIs('admin.orders.all') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>All Orders</span>
                        </a>
                    </div>
                </div>

                {{-- Agents --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Agents</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('admin.agents.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.agents.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.agents.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-users class="w-4 h-4 {{ request()->routeIs('admin.agents.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Agents</span>
                        </a>
                        <a href="{{ route('admin.accounts.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.accounts.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.accounts.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-cog class="w-4 h-4 {{ request()->routeIs('admin.accounts.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Account Management</span>
                        </a>
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Pricing</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('admin.pricing.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.pricing.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.pricing.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-tag class="w-4 h-4 {{ request()->routeIs('admin.pricing.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Pricing Rules</span>
                        </a>
                    </div>
                </div>

                {{-- Configuration --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Configuration</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('admin.config.payment') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.config.payment') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.config.payment') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-credit-card class="w-4 h-4 {{ request()->routeIs('admin.config.payment') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Payment Config</span>
                        </a>
                        <a href="{{ route('admin.config.paystack-charge') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.config.paystack-charge') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.config.paystack-charge') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-percent-badge class="w-4 h-4 {{ request()->routeIs('admin.config.paystack-charge') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Paystack Charges</span>
                        </a>
                        <a href="{{ route('admin.config.minimum-topup') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.config.minimum-topup') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.config.minimum-topup') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-banknotes class="w-4 h-4 {{ request()->routeIs('admin.config.minimum-topup') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Minimum Topup</span>
                        </a>
                        <a href="{{ route('admin.config.referral') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.config.referral') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.config.referral') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-share class="w-4 h-4 {{ request()->routeIs('admin.config.referral') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Referral Config</span>
                        </a>
                    </div>
                </div>

                {{-- API --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">API</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('admin.api-config') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.api-config') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.api-config') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-puzzle-piece class="w-4 h-4 {{ request()->routeIs('admin.api-config') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>API Config</span>
                        </a>
                        <a href="{{ route('admin.api-keys.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.api-keys.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.api-keys.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-key class="w-4 h-4 {{ request()->routeIs('admin.api-keys.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>API Keys</span>
                        </a>
                        <a href="{{ route('admin.api-config') }}#docs"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 bg-slate-100/80">
                                <x-heroicon-o-book-open class="w-4 h-4 text-slate-400" />
                            </div>
                            <span>API Documentation</span>
                        </a>
                    </div>
                </div>

                {{-- Communication --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Communication</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('admin.notifications.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.notifications.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.notifications.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-bell class="w-4 h-4 {{ request()->routeIs('admin.notifications.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Notifications</span>
                        </a>
                        <a href="{{ route('admin.banners.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.banners.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.banners.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-megaphone class="w-4 h-4 {{ request()->routeIs('admin.banners.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Banners</span>
                        </a>
                    </div>
                </div>

                {{-- Shops --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Shops</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('admin.shops.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.shops.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.shops.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-building-storefront class="w-4 h-4 {{ request()->routeIs('admin.shops.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Shops</span>
                        </a>
                        <a href="{{ route('admin.shop-orders.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.shop-orders.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.shop-orders.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-document-text class="w-4 h-4 {{ request()->routeIs('admin.shop-orders.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Shop Orders</span>
                        </a>
                        <a href="{{ route('admin.shop-withdrawals.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.shop-withdrawals.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.shop-withdrawals.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-banknotes class="w-4 h-4 {{ request()->routeIs('admin.shop-withdrawals.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Shop Withdrawals</span>
                        </a>
                    </div>
                </div>

                {{-- Finance --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Finance</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('admin.balance-history.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.balance-history.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.balance-history.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-clock class="w-4 h-4 {{ request()->routeIs('admin.balance-history.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Balance History</span>
                        </a>
                    </div>
                </div>

                {{-- Analytics --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">Analytics</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('admin.analytics.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.analytics.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.analytics.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-chart-pie class="w-4 h-4 {{ request()->routeIs('admin.analytics.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Analytics</span>
                        </a>
                        <a href="{{ route('admin.user-activity.index') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.user-activity.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.user-activity.*') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-clock class="w-4 h-4 {{ request()->routeIs('admin.user-activity.*') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>User Activity</span>
                        </a>
                    </div>
                </div>

                {{-- System --}}
                <div class="pt-4">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-300">System</p>
                    <div class="mt-1 space-y-0.5">
                        <a href="{{ route('admin.config.data-integration') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.config.data-integration') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.config.data-integration') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-circle-stack class="w-4 h-4 {{ request()->routeIs('admin.config.data-integration') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Data Integration</span>
                        </a>
                        <a href="{{ route('admin.config.low-balance-alert') }}"
                           class="nav-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.config.low-balance-alert') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 {{ request()->routeIs('admin.config.low-balance-alert') ? 'bg-[#2563EB]/15' : 'bg-slate-100/80' }}">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4 {{ request()->routeIs('admin.config.low-balance-alert') ? 'text-[#2563EB]' : 'text-slate-400' }}" />
                            </div>
                            <span>Low Balance Alert</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="border-t border-slate-100/80 px-3 py-3 space-y-1">
            <a href="{{ route('admin.profile.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.profile.*') ? 'bg-gradient-to-r from-[#2563EB]/10 to-[#2563EB]/5 text-[#2563EB]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#2563EB] to-[#60A5FA] flex items-center justify-center text-white text-[11px] font-bold shadow-sm">
                    {{ strtoupper(substr(session('admin_username', 'A'), 0, 1)) }}
                </div>
                <span class="flex-1">{{ session('admin_username', 'Admin') }}</span>
                <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300" />
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-400 hover:text-red-500 hover:bg-red-50/80 transition-all duration-200">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100/80 group-hover:bg-red-100">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                    </div>
                    <span>Sign out</span>
                </button>
            </form>
        </div>
    </div>
</aside>
