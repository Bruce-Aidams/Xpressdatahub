@props(['class' => ''])

<div {{ $attributes->merge(['class' => ' ' . $class]) }} x-data="{ activeTab: '{{ $active ?? '' }}' }">
    {{ $slot }}
</div>
