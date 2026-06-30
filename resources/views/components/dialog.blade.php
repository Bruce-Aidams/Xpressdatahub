@props(['id' => 'modal-' . uniqid()])

<div x-data="{ open: false }" @open-dialog.window="open = true" @close-dialog.window="open = false">
    {{-- Trigger --}}
    {{ $trigger ?? '' }}

    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black/80"
        style="display: none;"
        @click="open = false"
    ></div>

    {{-- Dialog --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border border-border bg-card p-6 shadow-lg rounded-xl"
        style="display: none;"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
    >
        <div class="flex flex-col space-y-1.5 text-center sm:text-left">
            @if(isset($title))
                <h2 class="text-lg font-semibold leading-none tracking-tight">{{ $title }}</h2>
            @endif
            @if(isset($description))
                <p class="text-sm text-muted-foreground">{{ $description }}</p>
            @endif
        </div>
        <div>{{ $slot }}</div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
            {{ $footer ?? '' }}
        </div>
    </div>
</div>
