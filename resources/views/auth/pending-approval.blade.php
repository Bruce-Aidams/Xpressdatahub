@extends('layouts.app')
@section('title', 'Account Pending Approval - Xpressdatahub')
@section('body')

<div class="min-h-screen flex items-center justify-center bg-[#FAFAFA] font-sans p-4 sm:p-6 lg:p-8">

    <div class="w-full max-w-5xl bg-white rounded-[2rem] shadow-2xl overflow-hidden flex flex-col lg:flex-row min-h-[580px]">

        <div class="w-full lg:w-[45%] flex flex-col justify-center items-center px-6 sm:px-10 lg:px-14 py-10">
            <div class="w-full max-w-sm">

                <div class="lg:hidden flex justify-center mb-6">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-14 h-14 rounded-xl shadow-md">
                </div>

                <div class="text-center mb-7">
                    <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                        <x-heroicon-o-clock class="w-8 h-8 text-amber-600" />
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Account Pending</h1>
                    <p class="text-slate-500 text-sm mt-2">Your account is awaiting admin approval</p>
                </div>

                @if(session('success'))
                    <div class="mb-5 p-3.5 rounded-xl flex items-center gap-3 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-100">
                        <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-500 shrink-0" /> {{ session('success') }}
                    </div>
                @endif

                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 mb-6">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                            <x-heroicon-o-information-circle class="w-5 h-5 text-amber-600" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">What happens next?</h3>
                            <ul class="mt-2 space-y-2 text-xs text-slate-500">
                                <li class="flex items-start gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></span>
                                    Our team will review your registration
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></span>
                                    You'll receive access within 24 hours
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></span>
                                    Once approved, you can log in and start trading
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="text-center space-y-3">
                    <a href="{{ route('login') }}" class="block w-full py-3 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl transition">
                        Back to Login
                    </a>
                    <p class="text-xs text-slate-400">
                        Questions? Contact <a href="mailto:support@xpressdatahub.com" class="text-[#2563EB] font-medium hover:underline">support@xpressdatahub.com</a>
                    </p>
                </div>

            </div>
        </div>

        <div class="hidden lg:flex lg:w-[55%] bg-gradient-to-br from-[#2563EB] to-[#1D4ED8] items-center justify-center p-12 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-20 left-10 w-32 h-32 border-2 border-white rounded-full"></div>
                <div class="absolute bottom-20 right-10 w-48 h-48 border-2 border-white rounded-full"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 border border-white rounded-full"></div>
            </div>
            <div class="relative z-10 text-center text-white">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-20 h-20 rounded-2xl shadow-2xl mx-auto mb-6">
                <h2 class="text-2xl font-black mb-3">Xpressdatahub</h2>
                <p class="text-blue-100 text-sm">Your trusted data bundle vendor platform</p>
            </div>
        </div>

    </div>

</div>

@endsection
