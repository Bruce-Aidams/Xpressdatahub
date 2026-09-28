<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 – Under Maintenance · Xpressdatahub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        .maintenance-bg {
            background:
                radial-gradient(ellipse at 20% 50%, rgba(234,88,12,0.12) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(251,146,60,0.10) 0%, transparent 50%),
                #fff7ed;
        }

        .stripe-bg {
            background-image: repeating-linear-gradient(
                -45deg,
                rgba(234,88,12,0.04) 0px,
                rgba(234,88,12,0.04) 10px,
                transparent 10px,
                transparent 20px
            );
        }

        @keyframes spin-gear  { to { transform: rotate(360deg); } }
        @keyframes spin-gear2 { to { transform: rotate(-360deg); } }
        @keyframes progress   { from { width: 0%; } to { width: 72%; } }
        @keyframes bounce-wrench {
            0%, 100% { transform: rotate(-20deg); }
            50%       { transform: rotate(20deg); }
        }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }

        .spin-gear  { animation: spin-gear  6s linear infinite; }
        .spin-gear2 { animation: spin-gear2 4s linear infinite; }
        .progress-bar { animation: progress 2.5s ease-out forwards; }
        .bounce-wrench { animation: bounce-wrench 1.2s ease-in-out infinite; transform-origin: bottom right; }
        .float { animation: float 3s ease-in-out infinite; }

        .segmented-display {
            font-family: 'Courier New', monospace;
            letter-spacing: 0.15em;
        }
    </style>
</head>
<body class="maintenance-bg min-h-screen flex flex-col items-center justify-center px-4 py-12 relative overflow-hidden">

    {{-- Diagonal stripe overlay --}}
    <div class="absolute inset-0 stripe-bg pointer-events-none opacity-60"></div>

    {{-- Decorative gear cluster top-right --}}
    <div class="absolute top-8 right-8 opacity-10 pointer-events-none hidden sm:block">
        <div class="spin-gear" style="font-size:64px">⚙</div>
    </div>
    <div class="absolute top-20 right-20 opacity-10 pointer-events-none hidden sm:block">
        <div class="spin-gear2" style="font-size:40px">⚙</div>
    </div>

    {{-- Main card --}}
    <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden border border-orange-100 z-10">

        {{-- Orange top stripe --}}
        <div class="h-2 w-full bg-gradient-to-r from-[#EA580C] via-orange-400 to-amber-400"></div>

        {{-- Header with logo --}}
        <div class="flex items-center justify-between px-8 pt-6 pb-4 border-b border-orange-50">
            <span class="text-xl font-black text-[#EA580C] tracking-tight">Xpressdatahub</span>
            <span class="flex items-center gap-1.5 px-3 py-1 bg-orange-100 text-orange-600 text-xs font-bold rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                MAINTENANCE
            </span>
        </div>

        <div class="px-8 py-8 text-center">

            {{-- Animated wrench + gears illustration --}}
            <div class="relative mx-auto mb-8 w-40 h-40">
                {{-- Background gear big --}}
                <div class="spin-gear absolute top-2 left-2 text-orange-200" style="font-size:80px; line-height:1;">⚙</div>
                {{-- Background gear small --}}
                <div class="spin-gear2 absolute bottom-4 right-0 text-orange-100" style="font-size:52px; line-height:1;">⚙</div>
                {{-- Foreground wrench --}}
                <div class="float absolute inset-0 flex items-center justify-center">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-[#EA580C] to-orange-400 shadow-xl shadow-orange-300/50 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white bounce-wrench" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.492-3.053c.217-.266.307-.611.25-.945l-.462-2.735a3.15 3.15 0 00-4.63-2.127l-3.6 2.25a.75.75 0 00-.336.75l.115 1.488-1.579 1.579a.75.75 0 000 1.06l3.536 3.536a.75.75 0 001.06 0l1.58-1.579 1.487.115a.75.75 0 00.75-.336l2.25-3.6a3.15 3.15 0 00-2.128-4.63l-2.735-.462c-.334-.057-.679.033-.945.25l-3.053 2.492"/>
                        </svg>
                    </div>
                </div>
            </div>

            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 mb-2 leading-tight">Scheduled Maintenance</h1>
            <p class="text-slate-500 text-sm mb-8 max-w-sm mx-auto leading-relaxed">
                We're upgrading our systems to serve you better. We'll be back before you know it!
            </p>

            {{-- Progress bar --}}
            <div class="mb-6 text-left">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Progress</span>
                    <span class="text-xs font-black text-orange-600 segmented-display">72%</span>
                </div>
                <div class="w-full h-3 bg-orange-100 rounded-full overflow-hidden">
                    <div class="progress-bar h-full bg-gradient-to-r from-[#EA580C] to-orange-400 rounded-full"></div>
                </div>
            </div>

            {{-- Status checklist --}}
            <div class="mb-8 text-left space-y-2">
                <div class="flex items-center gap-3 px-4 py-2.5 bg-green-50 border border-green-100 rounded-xl">
                    <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-semibold text-green-700">Database migration complete</span>
                </div>
                <div class="flex items-center gap-3 px-4 py-2.5 bg-green-50 border border-green-100 rounded-xl">
                    <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-semibold text-green-700">Security patches applied</span>
                </div>
                <div class="flex items-center gap-3 px-4 py-2.5 bg-orange-50 border border-orange-100 rounded-xl">
                    <svg class="w-4 h-4 text-orange-500 animate-spin shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span class="text-sm font-semibold text-orange-700">Optimizing payment gateway…</span>
                </div>
                <div class="flex items-center gap-3 px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl">
                    <div class="w-4 h-4 rounded-full border-2 border-slate-200 shrink-0"></div>
                    <span class="text-sm font-semibold text-slate-400">Final testing & deployment</span>
                </div>
            </div>

            {{-- WhatsApp CTA --}}
            <a href="https://whatsapp.com/channel/0029VbDOIdHDuMRhLmNkuP22" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2.5 w-full px-6 py-3.5 bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold text-sm rounded-2xl shadow-lg shadow-[#25D366]/30 transition-all duration-200 hover:-translate-y-0.5 active:scale-95 mb-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                Get Notified on WhatsApp
            </a>
            <p class="text-xs text-slate-400">We'll let you know the moment we're back online</p>
        </div>

        {{-- Bottom stripe --}}
        <div class="h-1 w-full bg-gradient-to-r from-amber-400 via-orange-400 to-[#EA580C]"></div>
    </div>

    <p class="mt-6 text-center text-xs text-orange-900/40 z-10">
        &copy; {{ date('Y') }} <span class="font-semibold text-orange-900/60">Xpressdatahub</span> · Affordable, Fast & Reliable Data
    </p>

</body>
</html>
