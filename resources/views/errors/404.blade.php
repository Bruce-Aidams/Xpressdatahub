<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 – Lost in Space · Xpressdatahub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        .starfield {
            background: #0f0c29;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
        }

        .star {
            position: absolute;
            border-radius: 50%;
            background: white;
            animation: twinkle var(--d, 3s) ease-in-out infinite;
            opacity: var(--o, 0.7);
        }

        @keyframes twinkle {
            0%, 100% { opacity: var(--o, 0.7); transform: scale(1); }
            50% { opacity: 0.2; transform: scale(0.6); }
        }

        @keyframes float-astronaut {
            0% { transform: translateY(0) rotate(-5deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0) rotate(-5deg); }
        }

        @keyframes orbit {
            from { transform: rotate(0deg) translateX(90px) rotate(0deg); }
            to   { transform: rotate(360deg) translateX(90px) rotate(-360deg); }
        }

        .float-astronaut { animation: float-astronaut 6s ease-in-out infinite; }
        .orbit { animation: orbit 8s linear infinite; }

        .giant-404 {
            font-size: clamp(120px, 20vw, 200px);
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, #a78bfa, #7c3aed, #c4b5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            opacity: 0.15;
            user-select: none;
            pointer-events: none;
        }
    </style>
</head>
<body class="starfield min-h-screen flex items-center justify-center px-4 py-12 relative overflow-hidden">

    {{-- Stars --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="star" style="width:2px;height:2px;top:10%;left:15%;--d:2s;--o:0.8"></div>
        <div class="star" style="width:3px;height:3px;top:20%;left:70%;--d:3.5s;--o:0.6"></div>
        <div class="star" style="width:2px;height:2px;top:35%;left:40%;--d:2.8s;--o:0.9"></div>
        <div class="star" style="width:1px;height:1px;top:55%;left:85%;--d:4s;--o:0.7"></div>
        <div class="star" style="width:3px;height:3px;top:70%;left:25%;--d:2.2s;--o:0.8"></div>
        <div class="star" style="width:2px;height:2px;top:80%;left:60%;--d:3.8s;--o:0.5"></div>
        <div class="star" style="width:2px;height:2px;top:15%;left:90%;--d:2.5s;--o:0.9"></div>
        <div class="star" style="width:1px;height:1px;top:45%;left:5%;--d:3.2s;--o:0.6"></div>
        <div class="star" style="width:3px;height:3px;top:90%;left:45%;--d:1.8s;--o:0.7"></div>
        <div class="star" style="width:2px;height:2px;top:60%;left:75%;--d:4.2s;--o:0.8"></div>
        <div class="star" style="width:1px;height:1px;top:5%;left:50%;--d:2.7s;--o:0.9"></div>
        <div class="star" style="width:2px;height:2px;top:75%;left:10%;--d:3.1s;--o:0.6"></div>
    </div>

    <div class="relative w-full max-w-2xl text-center z-10">

        {{-- Giant 404 background text --}}
        <div class="giant-404 absolute inset-0 flex items-center justify-center select-none" aria-hidden="true">404</div>

        {{-- Orbiting planet + astronaut --}}
        <div class="relative mx-auto mb-6 inline-block" style="width:200px;height:200px;">
            {{-- Planet --}}
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-24 rounded-full bg-gradient-to-br from-violet-400 to-purple-700 shadow-[0_0_60px_rgba(139,92,246,0.5)]">
                {{-- Planet rings --}}
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-36 h-8 border-2 border-violet-300/30 rounded-full rotate-12"></div>
            </div>
            {{-- Orbiting astronaut --}}
            <div class="orbit absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                <div class="float-astronaut text-3xl">🚀</div>
            </div>
        </div>

        <div class="mb-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-violet-500/20 border border-violet-500/30 text-violet-300 text-xs font-bold rounded-full tracking-widest uppercase backdrop-blur-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse"></span>
                Error 404
            </span>
        </div>

        <h1 class="text-4xl sm:text-5xl font-black text-white mb-3 leading-tight">Lost in Space</h1>
        <p class="text-violet-200/70 text-base mb-10 max-w-sm mx-auto leading-relaxed">
            Houston, we have a problem. The page you're looking for has drifted out of orbit and can't be found.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-violet-500 hover:bg-violet-400 text-white font-bold text-sm rounded-2xl shadow-lg shadow-violet-500/40 transition-all duration-200 hover:-translate-y-0.5 active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Return to Earth
            </a>
            <button onclick="history.back()"
                class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 border border-white/20 hover:bg-white/20 text-white font-bold text-sm rounded-2xl backdrop-blur-sm transition-all duration-200 hover:-translate-y-0.5 active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Go Back
            </button>
        </div>

        <p class="mt-12 text-xs text-violet-300/40">
            &copy; {{ date('Y') }} <span class="text-violet-300/60 font-semibold">Xpressdatahub</span> · Affordable, Fast & Reliable Data
        </p>

    </div>

</body>
</html>
