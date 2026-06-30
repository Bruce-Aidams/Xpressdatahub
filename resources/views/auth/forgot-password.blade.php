@extends('layouts.app')
@section('title', 'Forgot Password - Xpressdatahub')
@section('body')

<div class="min-h-screen flex items-center justify-center bg-[#FAFAFA] font-sans p-4 sm:p-6 lg:p-8">

    <div class="w-full max-w-5xl bg-white rounded-[2rem] shadow-2xl overflow-hidden flex flex-col lg:flex-row min-h-[580px]">

        <div class="w-full lg:w-[45%] flex flex-col justify-center items-center px-6 sm:px-10 lg:px-14 py-10">
            <div class="w-full max-w-sm">

                <div class="lg:hidden flex justify-center mb-6">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-14 h-14 rounded-xl shadow-md">
                </div>

                <div class="text-center mb-7">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Forgot Password</h1>
                    <p class="text-slate-500 text-sm mt-2">Enter your email to receive a password reset link</p>
                </div>

                @if(session('status'))
                    <div class="mb-5 p-3.5 rounded-xl flex items-center gap-3 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-100">
                        <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-500 shrink-0" /> {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl text-sm font-medium text-red-700 bg-red-50 border border-red-100 space-y-1">
                        @foreach($errors->all() as $error)
                            <p class="flex items-center gap-2"><x-heroicon-o-x-circle class="w-5 h-5 text-red-400 shrink-0" />{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-3.5">
                    @csrf

                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <x-heroicon-o-envelope class="w-5 h-5" />
                        </span>
                        <input type="email" name="email" required placeholder="Email Address" value="{{ old('email') }}"
                               class="w-full pl-10 pr-4 py-3 bg-[#F3F4F6] border-none rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#FF7A00]/20 transition-all">
                    </div>

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-[#7C2D12] to-[#C2410C] hover:from-[#652309] hover:to-[#9A3412] text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-orange-500/25">
                        Send Reset Link
                    </button>
                </form>

                <div class="flex items-center gap-4 my-6">
                    <div class="flex-1 h-px bg-slate-100"></div>
                    <p class="text-xs font-medium text-slate-400">or</p>
                    <div class="flex-1 h-px bg-slate-100"></div>
                </div>

                <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2.5 py-2.5 bg-white border border-slate-200 shadow-sm rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition">
                    <x-heroicon-o-arrow-left class="w-5 h-5 text-[#FF7A00]" />
                    Back to Login
                </a>

            </div>
        </div>

        <div class="hidden lg:flex lg:w-[55%] relative overflow-hidden bg-gradient-to-br from-[#7C2D12] via-[#9A3412] to-[#C2410C]">
            <svg class="absolute inset-0 w-full h-full object-cover opacity-20" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0,50 Q25,20 50,50 T100,50 L100,100 L0,100 Z" fill="#EA580C"></path>
                <path d="M0,70 Q25,40 50,70 T100,70 L100,100 L0,100 Z" fill="#ffffff" opacity="0.1"></path>
            </svg>
            <div class="relative z-10 w-full h-full flex items-center justify-center p-10">
                <div class="relative w-full max-w-md rounded-[2rem] border border-white/30 p-8 flex flex-col justify-between overflow-hidden min-h-[460px]"
                     style="background: rgba(255,255,255,0.1); backdrop-filter: blur(12px); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
                    <h2 class="text-3xl font-bold text-white leading-tight max-w-[260px]">
                        No worries. Enter your email and we'll send you a reset link.
                    </h2>
                    <div class="absolute bottom-0 right-0 w-[85%] h-[60%] rounded-tl-3xl overflow-hidden translate-x-4 translate-y-4">
                        <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&q=80&w=600&h=800" alt="Security" class="w-full h-full object-cover object-top opacity-90 mix-blend-luminosity hover:mix-blend-normal transition duration-500">
                    </div>
                    <div class="absolute top-1/2 -left-6 w-14 h-14 bg-white rounded-full flex items-center justify-center shadow-xl border-4 border-[#FF7A00]">
                        <x-heroicon-o-bolt class="w-6 h-6 text-[#FF7A00]" />
                    </div>
                    <div class="absolute top-5 right-5 w-11 h-11 rounded-xl overflow-hidden shadow-lg border-2 border-white/20">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
