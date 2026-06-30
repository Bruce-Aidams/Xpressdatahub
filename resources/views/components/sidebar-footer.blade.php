@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'border-t border-border px-3 py-4 ' . $class]) }}>
    {{ $slot }}
</div>
