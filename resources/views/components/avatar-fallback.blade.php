@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'flex h-full w-full items-center justify-center rounded-full bg-muted ' . $class]) }}>
    {{ $slot }}
</div>
