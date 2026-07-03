@extends('layouts.user')
@section('title', 'Change Password')
@section('page-title', 'Change Password')
@section('page-description', 'Update your account password')
@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Security Settings</h1>
        <p class="text-sm text-slate-400 mt-1">Update your account password to stay secure.</p>
    </div>

    <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <x-heroicon-o-lock-closed class="w-5 h-5 text-slate-400" />
            <h3 class="text-sm font-bold text-slate-800">Change Password</h3>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('user.password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Current Password *</label>
                    <div class="relative">
                        <x-heroicon-o-lock-closed class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300" />
                        <input type="password" name="current_password" required placeholder="Enter password"
                               class="pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] focus:ring-2 focus:ring-[#EA580C]/10 outline-none transition w-full">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">New Password *</label>
                    <div class="relative">
                        <x-heroicon-o-key class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300" />
                        <input type="password" name="password" required minlength="6" placeholder="Enter password"
                               class="pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] focus:ring-2 focus:ring-[#EA580C]/10 outline-none transition w-full">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Confirm Password *</label>
                    <div class="relative">
                        <x-heroicon-o-shield-check class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300" />
                        <input type="password" name="password_confirmation" required minlength="6" placeholder="Enter password"
                               class="pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#EA580C] focus:ring-2 focus:ring-[#EA580C]/10 outline-none transition w-full">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-bold rounded-xl px-6 py-2.5 transition shadow-md shadow-orange-500/10">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-6 bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <x-heroicon-o-information-circle class="w-5 h-5 text-slate-400" />
            <h3 class="text-sm font-bold text-slate-800">Password Tips</h3>
        </div>
        <div class="p-6">
            <ul class="space-y-2 text-sm text-slate-500">
                <li class="flex items-start gap-2">
                    <span class="text-[#EA580C] mt-0.5">•</span>
                    Use at least 8 characters for a stronger password
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-[#EA580C] mt-0.5">•</span>
                    Mix uppercase, lowercase, numbers, and symbols
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-[#EA580C] mt-0.5">•</span>
                    Avoid using personal information or common words
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-[#EA580C] mt-0.5">•</span>
                    Don't reuse passwords from other accounts
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
