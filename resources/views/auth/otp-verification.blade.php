@extends('layouts.app')
@section('title', 'Verify OTP - Xpressdatahub')
@section('body')

<div class="min-h-screen flex items-center justify-center bg-[#FAFAFA] font-sans p-4 sm:p-6 lg:p-8">

    <div class="w-full max-w-5xl bg-white rounded-[2rem] shadow-2xl overflow-hidden flex flex-col lg:flex-row min-h-[600px]">

        <!-- Left Side: Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-[#0359D0] overflow-hidden flex-col justify-center px-12">
            <div class="absolute -bottom-24 -left-24 w-96 h-96 border border-white/20 rounded-full"></div>
            <div class="absolute -bottom-12 -left-12 w-96 h-96 border border-white/10 rounded-full"></div>

            <div class="relative z-10">
                <h1 class="text-4xl font-bold text-white mb-3">Xpressdatahub</h1>
                <p class="text-blue-100 mb-8 text-sm max-w-[280px]">Affordable, Fast & Reliable Data Delivery</p>
            </div>
        </div>

        <!-- Right Side: OTP Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-16 py-10 relative">

            <div class="lg:hidden flex mb-6">
                <h1 class="text-3xl font-bold text-[#0359D0]">Xpressdatahub</h1>
            </div>

            <div class="mb-8">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Verify Code</h2>
                <p class="text-slate-600 text-sm mt-1">Enter the 6-digit code sent to your email</p>
            </div>

            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-xl text-sm font-medium text-red-700 bg-red-50 border border-red-100">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('password.otp.verify') }}" class="space-y-4 max-w-sm" id="otpForm">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="relative">
                    <input type="text" name="otp" required placeholder="Enter 6-digit code" maxlength="6"
                           class="w-full px-4 py-3.5 bg-white border border-gray-200 rounded-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#0359D0]/50 transition-all shadow-sm tracking-widest text-center"
                           pattern="\d{6}">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 bg-[#0359D0] hover:bg-blue-700 text-white text-sm font-medium rounded-full transition-all shadow-md shadow-blue-500/30">
                        Verify Code
                    </button>
                </div>
            </form>
            
        </div>

    </div>
</div>
@endsection
