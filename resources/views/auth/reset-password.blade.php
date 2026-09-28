@extends('layouts.app')
@section('title', 'Set New Password - Xpressdatahub')
@section('body')

<div class="min-h-screen flex items-center justify-center bg-[#FAFAFA] font-sans p-4 sm:p-6 lg:p-8">

    <div class="w-full max-w-5xl bg-white rounded-[2rem] shadow-2xl overflow-hidden flex flex-col lg:flex-row min-h-[600px]">

        <!-- Left Side: Blue Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-[#0359D0] overflow-hidden flex-col justify-center px-12">
            <!-- Decorative Circles -->
            <div class="absolute -bottom-24 -left-24 w-96 h-96 border border-white/20 rounded-full"></div>
            <div class="absolute -bottom-12 -left-12 w-96 h-96 border border-white/10 rounded-full"></div>

            <div class="relative z-10">
                <h1 class="text-4xl font-bold text-white mb-3">Xpressdatahub</h1>
                <p class="text-blue-100 mb-8 text-sm max-w-[280px]">Affordable, Fast & Reliable Data Delivery</p>
            </div>
        </div>

        <!-- Right Side: Reset Password Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-16 py-10 relative">

            <!-- Mobile Logo (Visible only on mobile if needed) -->
            <div class="lg:hidden flex mb-6">
                <h1 class="text-3xl font-bold text-[#0359D0]">Xpressdatahub</h1>
            </div>

            <div class="mb-8">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">New Password</h2>
                <p class="text-slate-600 text-sm mt-1">Choose a strong new password for your account</p>
            </div>

            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-xl text-sm font-medium text-red-700 bg-red-50 border border-red-100 space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="flex items-center gap-2"><x-heroicon-o-x-circle class="w-5 h-5 text-red-400 shrink-0" />{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.reset.post') }}" class="space-y-4 max-w-sm" id="resetForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <x-heroicon-o-envelope class="w-4 h-4" />
                    </span>
                    <input type="email" value="{{ $email }}" disabled
                           class="w-full pl-11 pr-4 py-3.5 bg-gray-100 border-none rounded-full text-sm text-gray-400 cursor-not-allowed shadow-sm">
                </div>

                <div>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <x-heroicon-o-lock-closed class="w-4 h-4" />
                        </span>
                        <input type="password" name="password" id="resetPass" required placeholder="New Password"
                               oninput="xdh_checkStrength(this.value)"
                               class="w-full pl-11 pr-12 py-3.5 bg-white border {{ $errors->has('password') ? 'border-red-400 ring-2 ring-red-100' : 'border-gray-200' }} rounded-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#0359D0]/50 transition-all shadow-sm">
                        <button type="button" onclick="xdh_togglePass('resetPass','resetPassIcon')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" tabindex="-1">
                            <span id="resetPassIcon"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg></span>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 ml-4 text-xs text-red-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    {{-- Password Strength Meter --}}
                    <div id="xdh_strength" class="hidden mt-2 px-1">
                        <div class="flex gap-1 mb-1">
                            <div id="xs1" class="h-1 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                            <div id="xs2" class="h-1 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                            <div id="xs3" class="h-1 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                            <div id="xs4" class="h-1 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                            <div id="xs5" class="h-1 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                        </div>
                        <p id="xs_label" class="text-xs font-medium"></p>
                    </div>
                </div>

                <div>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <x-heroicon-o-lock-closed class="w-4 h-4" />
                        </span>
                        <input type="password" name="password_confirmation" id="resetPassConf" required placeholder="Confirm Password"
                               class="w-full pl-11 pr-12 py-3.5 bg-white border {{ $errors->has('password_confirmation') ? 'border-red-400 ring-2 ring-red-100' : 'border-gray-200' }} rounded-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#0359D0]/50 transition-all shadow-sm">
                        <button type="button" onclick="xdh_togglePass('resetPassConf','resetPassConfIcon')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" tabindex="-1">
                            <span id="resetPassConfIcon"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg></span>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="mt-1.5 ml-4 text-xs text-red-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 bg-[#0359D0] hover:bg-blue-700 text-white text-sm font-medium rounded-full transition-all shadow-md shadow-blue-500/30">
                        Save Password
                    </button>
                </div>
                
                <div class="flex justify-center pt-2">
                    <a href="{{ route('login') }}" class="text-xs font-medium text-gray-500 hover:text-[#0359D0] transition">Back to Login</a>
                </div>
            </form>
            
            <div class="mt-8 max-w-sm flex justify-center">
                <a href="https://whatsapp.com/channel/0029VbDOIdHDuMRhLmNkuP22" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm text-[#25D366] hover:text-green-600 font-medium transition-colors bg-green-50 px-4 py-2 rounded-full">
                    <svg class="w-5 h-5 animate-bounce" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    Join our WhatsApp Channel
                </a>
            </div>
            
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
function xdh_togglePass(fieldId, iconId) {
    const p = document.getElementById(fieldId);
    const i = document.getElementById(iconId);
    const isHidden = p.type === 'password';
    p.type = isHidden ? 'text' : 'password';
    i.innerHTML = isHidden
        ? '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>'
        : '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>';
}

function xdh_checkStrength(pw) {
    const box = document.getElementById('xdh_strength');
    if (!pw) { box.classList.add('hidden'); return; }
    box.classList.remove('hidden');
    const score = [
        pw.length >= 8,
        /[a-z]/.test(pw),
        /[A-Z]/.test(pw),
        /\d/.test(pw),
        /[^A-Za-z0-9]/.test(pw)
    ].filter(Boolean).length;
    const colors = ['bg-red-500','bg-red-500','bg-orange-400','bg-yellow-400','bg-lime-500','bg-emerald-500'];
    const labels = ['Very Weak','Very Weak','Weak','Fair','Good','Strong'];
    const lColors = ['text-red-500','text-red-500','text-orange-400','text-yellow-500','text-lime-500','text-emerald-500'];
    for (let i = 1; i <= 5; i++) {
        document.getElementById('xs'+i).className = 'h-1 flex-1 rounded-full transition-all duration-300 ' + (i <= score ? colors[score] : 'bg-slate-200');
    }
    const lbl = document.getElementById('xs_label');
    lbl.textContent = labels[score];
    lbl.className = 'text-xs font-medium ' + lColors[score];
}

document.getElementById('resetForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Please wait...';
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-not-allowed');
});
</script>
@endpush
