@extends('layouts.admin')
@section('page-title', 'My Profile')
@section('page-description', 'Manage your administrator account settings')
@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Profile Settings</h1>
        <p class="text-sm text-slate-400 mt-1">Update your personal information and contact details.</p>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-[#2563EB] via-[#60A5FA] to-[#93C5FD] p-6">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-2xl bg-white border-4 border-white shadow-lg flex items-center justify-center">
                    <span class="text-2xl font-black text-[#2563EB]">{{ strtoupper(substr($admin->full_name ?? 'A', 0, 1)) }}</span>
                </div>
                <div class="text-white">
                    <h2 class="text-lg font-bold">{{ $admin->full_name ?? 'Admin User' }}</h2>
                    <p class="text-sm text-white/80">{{ $admin->email ?? 'admin@example.com' }}</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-[#2563EB]">
                <x-heroicon-o-user-circle class="w-5 h-5" />
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Personal Information</h3>
                <p class="text-xs text-slate-400">Update your name and email address.</p>
            </div>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Full Name</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><x-heroicon-o-user class="w-5 h-5" /></span>
                        <input type="text" name="full_name" value="{{ $admin->full_name ?? '' }}" placeholder="Enter your full name"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white transition"
                               onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'"
                               onblur="this.style.borderColor=''; this.style.boxShadow=''">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><x-heroicon-o-envelope class="w-5 h-5" /></span>
                        <input type="email" name="email" value="{{ $admin->email ?? '' }}" required placeholder="admin@example.com"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white transition"
                               onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'"
                               onblur="this.style.borderColor=''; this.style.boxShadow=''">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl px-6 py-2.5 transition shadow-md shadow-blue-500/10 flex items-center justify-center gap-2">
                        <x-heroicon-o-arrow-up-tray class="w-5 h-5" /> Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
