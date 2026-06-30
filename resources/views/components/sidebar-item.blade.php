@props([
    'href' => '#',
    'active' => false,
    'class' => '',
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ' .
            ($active
                ? 'bg-accent text-accent-foreground'
                : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground')
            . ' ' . $class
    ]) }}
>
    {{ $slot }}
</a>
