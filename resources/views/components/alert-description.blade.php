@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'text-sm [&_p]:leading-relaxed ' . $class]) }}>
    {{ $slot }}
</div>
