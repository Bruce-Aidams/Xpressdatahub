@props([
    'orientation' => 'horizontal',
    'class' => '',
])

@php
    $classes = match($orientation) {
        'vertical' => 'h-full w-[1px]',
        default => 'h-[1px] w-full',
    } . ' shrink-0 bg-border ' . $class;
@endphp

<div role="none" {{ $attributes->merge(['class' => $classes]) }}></div>
