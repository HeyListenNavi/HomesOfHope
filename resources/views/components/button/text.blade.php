@props([
    'href' => null,
    'type' => 'button',
])

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center text-body-small bg-none hover:text-foreground-secondary transition-colors cursor-pointer']) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'flex items-center text-body-small bg-none hover:text-foreground-secondary transition-colors cursor-pointer']) }}>
        {{ $slot }}
    </button>
@endif
