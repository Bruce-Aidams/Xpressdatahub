@props(['network'])

@php
    $colors = [
        'MTN' => [
            'bg' => 'bg-amber-50',
            'text' => 'text-amber-700',
            'border' => 'border-amber-200',
            'dot' => 'bg-amber-500',
        ],
        'Telecel' => [
            'bg' => 'bg-red-50',
            'text' => 'text-red-700',
            'border' => 'border-red-200',
            'dot' => 'bg-red-500',
        ],
        'AirtelTigo' => [
            'bg' => 'bg-blue-50',
            'text' => 'text-blue-700',
            'border' => 'border-blue-200',
            'dot' => 'bg-blue-500',
        ],
    ];
    $style = $colors[$network] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'];
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 {{ $style['bg'] }} {{ $style['text'] }} border {{ $style['border'] }} rounded-full text-[10px] sm:text-xs font-bold">
    <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"></span>
    {{ $network }}
</span>
