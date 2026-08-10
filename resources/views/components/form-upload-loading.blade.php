@props([
    'text' => null,
])

<div
    wire:loading
    {{ $attributes->merge(['class' => 'text-center']) }}
    x-cloak
>
    <i class='bx bx-loader-alt bx-spin text-5xl text-white'></i>
    @if ($text)
        <p class="text-2xl font-bold text-white">{{ $text }}</p>
    @endif
</div>
