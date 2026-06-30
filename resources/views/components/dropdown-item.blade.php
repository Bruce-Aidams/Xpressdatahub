@props(['class' => '', 'as' => 'a'])

@if($as === 'button')
    <button {{ $attributes->merge(['class' => 'relative flex w-full cursor-pointer select-none items-center rounded-xl px-3 py-2 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground ' . $class]) }}>
        {{ $slot }}
    </button>
@else
    <a {{ $attributes->merge(['class' => 'relative flex cursor-pointer select-none items-center rounded-xl px-3 py-2 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground ' . $class]) }}>
        {{ $slot }}
    </a>
@endif
