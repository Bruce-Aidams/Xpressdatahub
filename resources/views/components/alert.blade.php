@props(['variant' => 'default', 'class' => ''])

@php
    $variants = [
        'default' => 'bg-background text-foreground',
        'destructive' => 'border-destructive/50 text-destructive dark:border-destructive',
    ];

    $classes = 'relative w-full rounded-xl border p-4 ' . ($variants[$variant] ?? $variants['default']) . ' ' . $class;
@endphp

<div role="alert" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
