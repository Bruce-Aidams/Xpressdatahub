@props([
    'value' => '',
    'class' => '',
])

<div
    x-show="activeTab === '{{ $value }}'"
    x-bind:class="activeTab === '{{ $value }}' ? '' : 'hidden'"
    {{ $attributes->merge(['class' => 'mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ' . $class]) }}
>
    {{ $slot }}
</div>
