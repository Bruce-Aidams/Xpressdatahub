@props(['status'])

@php
    $colors = [
        'delivered' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
        'processing' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-200', 'dot' => 'bg-blue-500'],
        'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
        'failed' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'border' => 'border-red-200', 'dot' => 'bg-red-500'],
        'cancelled' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-500', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'],
        'verified' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'border' => 'border-violet-200', 'dot' => 'bg-violet-500'],
    ];
    $style = $colors[strtolower($status)] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'];
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 {{ $style['bg'] }} {{ $style['text'] }} border {{ $style['border'] }} rounded-full text-[10px] sm:text-xs font-bold">
    <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"></span>
    {{ ucfirst($status) }}
</span>
