<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 – Server Error · Xpressdatahub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        .crash-bg {
            background: #fefce8;
            background-image:
                linear-gradient(rgba(234,179,8,.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(234,179,8,.08) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        @keyframes glitch {
            0%, 100% { clip-path: inset(0 0 98% 0); transform: translate(-4px, 0); }
            20%       { clip-path: inset(30% 0 50% 0); transform: translate(4px, 0); }
            40%       { clip-path: inset(60% 0 20% 0); transform: translate(-2px, 0); }
            60%       { clip-path: inset(10% 0 80% 0); transform: translate(3px, 0); }
            80%       { clip-path: inset(80% 0 5% 0); transform: translate(-3px, 0); }
        }

        @keyframes glitch2 {
            0%, 100% { clip-path: inset(90% 0 0 0); transform: translate(4px, 0); opacity: 0.5; }
            20%       { clip-path: inset(50% 0 40% 0); transform: translate(-4px, 0); }
            40%       { clip-path: inset(20% 0 70% 0); transform: translate(2px, 0); }
            60%       { clip-path: inset(75% 0 10% 0); transform: translate(-2px, 0); }
            80%       { clip-path: inset(5% 0 90% 0); transform: translate(3px, 0); }
        }

        @keyframes shake-frame {
            0%, 100% { transform: translate(0,0) rotate(0deg); }
            25% { transform: translate(-2px, 1px) rotate(-0.5deg); }
            50% { transform: translate(2px, -1px) rotate(0.5deg); }
            75% { transform: translate(-1px, 2px) rotate(-0.3deg); }
        }

        @keyframes fall {
            0% { transform: translateY(-10px) rotate(0deg); opacity: 0; }
            100% { transform: translateY(0px) rotate(var(--r, 10deg)); opacity: 1; }
        }

        .glitch-wrap { position: relative; display: inline-block; }
        .glitch-wrap::before, .glitch-wrap::after {
            content: attr(data-text);
            position: absolute; inset: 0;
            font-size: inherit; font-weight: inherit;
            color: inherit;
        }
        .glitch-wrap::before {
            color: #f97316;
            animation: glitch 3s steps(1) infinite;
        }
        .glitch-wrap::after {
            color: #eab308;
            animation: glitch2 3s steps(1) infinite;
        }

        .shake-card { animation: shake-frame 0.15s ease-in-out infinite; }

        .fragment {
            animation: fall 0.5s ease-out forwards;
            animation-delay: var(--delay, 0s);
            opacity: 0;
        }
    </style>
</head>
<body class="crash-bg min-h-screen flex items-center justify-center px-4 py-12 relative overflow-hidden">

    {{-- Decorative scattered bolts --}}
    <div class="absolute top-20 left-10 text-4xl fragment" style="--r: -15deg; --delay: 0.1s">⚙️</div>
    <div class="absolute top-32 right-16 text-3xl fragment" style="--r: 20deg; --delay: 0.3s">🔩</div>
    <div class="absolute bottom-24 left-20 text-3xl fragment" style="--r: -10deg; --delay: 0.5s">🔧</div>
    <div class="absolute bottom-32 right-12 text-4xl fragment" style="--r: 25deg; --delay: 0.2s">⚡</div>
    <div class="absolute top-16 left-1/3 text-2xl fragment" style="--r: -20deg; --delay: 0.4s">💥</div>
    <div class="absolute bottom-16 right-1/3 text-2xl fragment" style="--r: 15deg; --delay: 0.6s">🔥</div>

    <div class="relative w-full max-w-lg text-center z-10">

        {{-- Glitching 500 number --}}
        <div class="mb-6 select-none" aria-hidden="true">
            <span
                class="glitch-wrap text-[130px] sm:text-[160px] font-black leading-none text-amber-500"
                data-text="500"
            >500</span>
        </div>

        {{-- Shaking broken server card --}}
        <div class="shake-card inline-block mb-8 p-5 bg-white border-2 border-amber-200 rounded-2xl shadow-xl shadow-amber-200/50 relative">
            <div class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center">
                <span class="text-white text-[10px] font-black">!</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                    <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p class="text-xs font-bold text-amber-700 uppercase tracking-widest">Server Crash</p>
                    <p class="text-sm font-black text-slate-900">Internal Server Error</p>
                    <div class="mt-1 flex gap-1">
                        <span class="w-16 h-1.5 bg-red-200 rounded-full"></span>
                        <span class="w-8 h-1.5 bg-amber-200 rounded-full"></span>
                        <span class="w-4 h-1.5 bg-slate-100 rounded-full"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full tracking-widest uppercase">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                Error 500
            </span>
        </div>

        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 mb-3 leading-tight">Something Exploded</h1>
        <p class="text-slate-500 text-base mb-10 max-w-sm mx-auto leading-relaxed">
            Our servers had an unexpected meltdown. Engineers have been notified and are already rebuilding the pieces.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <button onclick="window.location.reload()"
               class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white font-bold text-sm rounded-2xl shadow-lg shadow-amber-400/30 transition-all duration-200 hover:-translate-y-0.5 active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Try Again
            </button>
            <a href="{{ url('/') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-sm rounded-2xl transition-all duration-200 hover:-translate-y-0.5 active:scale-95 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Go Home
            </a>
        </div>

        <p class="mt-12 text-xs text-slate-400">
            &copy; {{ date('Y') }} <span class="font-semibold text-slate-500">Xpressdatahub</span> · Affordable, Fast & Reliable Data
        </p>

    </div>

</body>
</html>
