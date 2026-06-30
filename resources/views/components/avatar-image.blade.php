@props(['class' => ''])

<img {{ $attributes->merge(['class' => 'aspect-square h-full w-full ' . $class]) }} />
