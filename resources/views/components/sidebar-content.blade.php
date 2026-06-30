@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'flex-1 overflow-y-auto px-3 py-4 scrollbar-thin ' . $class]) }}>
    {{ $slot }}
</div>
