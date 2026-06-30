¿@extends('layouts.user')
@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('page-description', 'Manage your account settings')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">My Profile</h1>
        <p class="text-sm text-slate-400 mt-1">Manage your account settings</p>
    </div>

    {{-- Profile Card --}}
    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        {{-- Gradient Header --}}
        <div class="bg-gradient-to-r from-[#EA580C] via-[#FB923C] to-[#FDBA74] p-6 relative">
            <div class="flex items-end gap-5">
                <div class="w-24 h-24 rounded-2xl bg-white border-4 border-white shadow-lg flex items-center justify-center shrink-0">
                    <span class="text-3xl font-black text-[#EA580C]">{{ strtoupper(substr($currentUser->first_name ?? $currentUser->username ?? 'U', 0, 1)) }}{{ strtoupper(substr($currentUser->last_name ?? '', 0, 1)) }}</span>
                </div>
                <div class="pb-1">
                    <h2 class="text-lg font-black text-white drop-shadow-sm">{{ $currentUser->first_name ?? '' }} {{ $currentUser->last_name ?? '' }}</h2>
                    <p class="text-sm text-white/80">{{ $currentUser->email ?? '' }}</p>
                </div>
            </div>
        </div>

        {{-- Info Section --}}
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-[#EA580C]/10 flex items-center justify-center shrink-0">
                        <x-heroicon-o-user class="text-[#EA580C] w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Username</p>
                        <p class="text-sm font-bold text-slate-800">{{ $currentUser->username ?? '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-[#EA580C]/10 flex items-center justify-center shrink-0">
                        <x-heroicon-o-shield-check class="text-[#EA580C] w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Role</p>
                        <p class="text-sm font-bold text-slate-800">{{ ucfirst($currentUser->role ?? 'agent') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-[#EA580C]/10 flex items-center justify-center shrink-0">
                        <x-heroicon-o-wallet class="text-[#EA580C] w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Balance</p>
                        <p class="text-sm font-bold text-slate-800">GHâ‚µ{{ number_format($currentUser->balance ?? 0, 2) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-[#EA580C]/10 flex items-center justify-center shrink-0">
                        <x-heroicon-o-shopping-bag class="text-[#EA580C] w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Orders</p>
                        <p class="text-sm font-bold text-slate-800">{{ $currentUser->orders_count ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-[#EA580C]/10 flex items-center justify-center shrink-0">
                        <x-heroicon-o-calendar class="text-[#EA580C] w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Member Since</p>
                        <p class="text-sm font-bold text-slate-800">{{ $currentUser->created_at?->format('M d, Y') ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-[#EA580C]/10 flex items-center justify-center shrink-0">
                        <x-heroicon-o-check-badge class="text-[#EA580C] w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</p>
                        <p class="text-sm font-bold {{ ($currentUser->status ?? 'active') === 'active' ? 'text-emerald-600' : 'text-red-600' }}">{{ ucfirst($currentUser->status ?? 'active') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Profile --}}
    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-[#EA580C]/10 flex items-center justify-center">
                <x-heroicon-o-pencil-square class="text-[#EA580C] w-5 h-5" />
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Personal Information</h3>
                <p class="text-[11px] text-slate-400">Update your name and contact details</p>
            </div>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('user.profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">First Name</label>
                        <input type="text" name="first_name" value="{{ $currentUser->first_name ?? '' }}" placeholder="Enter first name"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#EA580C] focus:ring-2 focus:ring-[#EA580C]/10 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Last Name</label>
                        <input type="text" name="last_name" value="{{ $currentUser->last_name ?? '' }}" placeholder="Enter last name"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#EA580C] focus:ring-2 focus:ring-[#EA580C]/10 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ $currentUser->email ?? '' }}" required placeholder="you@example.com"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#EA580C] focus:ring-2 focus:ring-[#EA580C]/10 outline-none transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Phone Number</label>
                        <input type="tel" name="phone" value="{{ $currentUser->phone ?? '' }}" placeholder="Enter phone number"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#EA580C] focus:ring-2 focus:ring-[#EA580C]/10 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                            Username <span class="normal-case font-normal text-slate-400">(read-only)</span>
                        </label>
                        <input type="text" value="{{ $currentUser->username ?? '' }}" disabled
                               class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center gap-3">
                    <button type="submit" class="bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-bold rounded-xl px-6 py-2.5 transition shadow-md shadow-orange-500/10 flex items-center gap-2">
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4" /> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('user.password.change') }}" class="bg-white border border-slate-100/80 rounded-2xl shadow-sm p-5 flex items-center gap-4 hover:border-[#EA580C]/30 hover:shadow-md transition-all group">
            <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center group-hover:bg-[#EA580C]/10 transition shrink-0">
                <x-heroicon-o-key class="text-amber-500 group-hover:text-[#EA580C] transition w-5 h-5" />
            </div>
            <div>
                <p class="text-sm font-bold text-slate-800">Change Password</p>
                <p class="text-[11px] text-slate-400">Update your account password</p>
            </div>
            <x-heroicon-o-chevron-right class="text-slate-300 ml-auto w-4 h-4 group-hover:text-[#EA580C] transition" />
        </a>
        <a href="{{ route('user.api-keys.index') }}" class="bg-white border border-slate-100/80 rounded-2xl shadow-sm p-5 flex items-center gap-4 hover:border-[#EA580C]/30 hover:shadow-md transition-all group">
            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-[#EA580C]/10 transition shrink-0">
                <x-heroicon-o-code-bracket class="text-blue-500 group-hover:text-[#EA580C] transition w-5 h-5" />
            </div>
            <div>
                <p class="text-sm font-bold text-slate-800">API Keys</p>
                <p class="text-[11px] text-slate-400">Manage your API access keys</p>
            </div>
            <x-heroicon-o-chevron-right class="text-slate-300 ml-auto w-4 h-4 group-hover:text-[#EA580C] transition" />
        </a>
    </div>
</div>
@endsection
