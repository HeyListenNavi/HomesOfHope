@props([
    'href' => null,
    'type' => 'button',
])

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center justify-center text-label-large border-3 border-background-light hover:border-background-light/80 hover:text-foreground/80 rounded-2xl px-6 py-4 transition-colors']) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'flex items-center justify-center text-label-large border-3 border-background-light hover:border-background-light/80 hover:text-foreground/80 rounded-2xl px-6 py-4 transition-colors cursor-pointer']) }}>
        {{ $slot }}
    </button>
@endif