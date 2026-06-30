<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white border border-slate-100/80 shadow-sm rounded-2xl p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-xl font-black text-slate-800 mb-2">Payment Successful!</h1>
            <p class="text-sm text-slate-500 mb-1">{{ session('success', 'Your order has been received and is being processed.') }}</p>
            @if(session('error'))
                <p class="text-sm text-red-500 mb-1">{{ session('error') }}</p>
            @endif
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[11px] text-slate-400">Reference: <span class="font-mono text-slate-600">{{ session('order_reference', 'N/A') }}</span></p>
            </div>
            <a href="{{ route('login') }}" class="mt-6 w-full inline-flex items-center justify-center gap-2 py-3 bg-[#EA580C] hover:bg-[#C2410C] text-white font-bold text-sm rounded-xl transition">
                Back to Login
            </a>
        </div>
    </div>
</body>
</html>
