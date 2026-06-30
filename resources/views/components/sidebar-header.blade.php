@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'flex h-16 items-center gap-2 border-b border-border px-6 ' . $class]) }}>
    {{ $slot }}
</div>
