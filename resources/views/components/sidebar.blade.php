@props(['class' => ''])

<aside
    id="sidebar"
    {{ $attributes->merge(['class' => 'fixed left-0 top-0 z-40 h-screen w-64 border-r border-border bg-sidebar transition-transform lg:translate-x-0 ' . $class]) }}
>
    <div class="flex h-full flex-col">
        {{ $slot }}
    </div>
</aside>
