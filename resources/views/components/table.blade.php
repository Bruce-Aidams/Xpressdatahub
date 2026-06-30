@props(['class' => ''])

<div class="relative w-full overflow-auto">
    <table {{ $attributes->merge(['class' => 'w-full caption-bottom text-sm ' . $class]) }}>
        {{ $slot }}
    </table>
</div>
