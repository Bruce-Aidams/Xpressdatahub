<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 – Access Denied · Xpressdatahub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        .vault-bg {
            background: #0a0a0a;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 39px, rgba(220,38,38,0.06) 39px, rgba(220,38,38,0.06) 40px),
                repeating-linear-gradient(90deg, transparent, transparent 39px, rgba(220,38,38,0.06) 39px, rgba(220,38,38,0.06) 40px);
        }

        @keyframes alarm-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.7), 0 0 40px rgba(220,38,38,0.3); }
            50% { box-shadow: 0 0 0 20px rgba(220,38,38,0), 0 0 80px rgba(220,38,38,0.5); }
        }

        @keyframes scan {
            0% { top: 0%; }
            100% { top: 100%; }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 50%, 90% { transform: translateX(-4px); }
            30%, 70% { transform: translateX(4px); }
        }

        @keyframes blink {
            0%, 49% { opacity: 1; }
            50%, 100% { opacity: 0; }
        }

        .alarm-pulse { animation: alarm-pulse 1.5s ease-in-out infinite; }
        .shake-lock { animation: shake 0.5s ease-in-out 1; }
        .cursor { animation: blink 1s step-end infinite; }

        .scanline {
            position: absolute;
            left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(220,38,38,0.6), transparent);
            animation: scan 3s linear infinite;
        }

        .red-glow { text-shadow: 0 0 20px rgba(220,38,38,0.8); }
    </style>
</head>
<body class="vault-bg min-h-screen flex items-center justify-center px-4 py-12 relative overflow-hidden">

    {{-- Red ambient corner lights --}}
    <div class="absolute top-0 left-0 w-64 h-64 bg-red-900/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-red-900/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative w-full max-w-lg z-10">

        {{-- Terminal-style top bar --}}
        <div class="flex items-center gap-2 mb-2 px-4 py-2 bg-red-950/50 border border-red-900/30 rounded-t-2xl">
            <div class="w-3 h-3 rounded-full bg-red-500"></div>
            <div class="w-3 h-3 rounded-full bg-yellow-500/50"></div>
            <div class="w-3 h-3 rounded-full bg-green-500/20"></div>
            <span class="ml-2 text-red-500/70 text-[11px] font-mono">security.sys — ACCESS CONTROL</span>
        </div>

        <div class="bg-black/60 border border-red-900/30 rounded-b-2xl rounded-tr-2xl p-8 backdrop-blur-sm text-center">

            {{-- Alarm lock --}}
            <div class="relative inline-block mb-8">
                <div class="alarm-pulse w-24 h-24 rounded-2xl bg-red-950 border-2 border-red-700 flex items-center justify-center mx-auto">
                    <svg class="w-12 h-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                {{-- Scan line --}}
                <div class="scanline"></div>
            </div>

            {{-- Code readout --}}
            <div class="mb-4 font-mono text-xs text-red-500/60 tracking-widest">
                <span>SECURITY_CODE :: </span><span class="text-red-500 font-bold">403</span><span class="cursor">_</span>
            </div>

            <h1 class="text-4xl sm:text-5xl font-black text-red-500 red-glow mb-1 leading-tight tracking-tight">ACCESS DENIED</h1>
            <div class="w-16 h-0.5 bg-red-700 mx-auto my-4"></div>

            <p class="text-red-300/60 text-sm mb-8 max-w-xs mx-auto leading-relaxed font-mono">
                &gt; Unauthorized access attempt logged.<br>
                &gt; Insufficient clearance level.<br>
                &gt; This incident has been recorded.
            </p>

            {{-- Auth level badges --}}
            <div class="flex justify-center gap-2 mb-8">
                <span class="px-2 py-1 bg-red-900/40 border border-red-800/50 text-red-500 text-[10px] font-mono rounded">LEVEL: NONE</span>
                <span class="px-2 py-1 bg-red-900/40 border border-red-800/50 text-red-500 text-[10px] font-mono rounded">REQUIRED: ADMIN</span>
                <span class="px-2 py-1 bg-red-900/40 border border-red-800/50 text-red-500 text-[10px] font-mono rounded">STATUS: BLOCKED</span>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-500 text-white font-bold text-sm rounded-xl transition-all duration-200 hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-red-900/50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Safe Exit
                </a>
                <button onclick="history.back()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-transparent border border-red-900/50 hover:border-red-700 text-red-500/70 hover:text-red-400 font-bold text-sm rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Go Back
                </button>
            </div>

        </div>

        <p class="mt-6 text-center text-xs text-red-900/60">
            &copy; {{ date('Y') }} <span class="text-red-900/80 font-semibold">Xpressdatahub</span>
        </p>

    </div>

</body>
</html>
