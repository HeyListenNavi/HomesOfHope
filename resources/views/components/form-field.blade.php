@props([
    'label' => null,
    'description' => '',
    'icon' => null,
    'required' => false,
    'optional' => false,
    'error' => '',
])

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if ($label || $description)
        <div class="space-y-1">
            @if ($label)
                <label class="flex items-center gap-1 text-2xl font-bold text-white md:text-3xl">
                    @if ($icon)
                        <i class='bx {{ $icon }} bx-md text-white/50'></i>
                    @endif
                    {{ $label }}
                    @if ($required)
                        <x-form-badge type="required" />
                    @elseif($optional)
                        <x-form-badge type="optional" />
                    @endif
                </label>
            @endif
            @if ($description)
                <p class="text-xl text-white/70">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}

    @if ($error)
        @if (is_array($error))
            @foreach ($error as $key)
                @if ($key && $errors->has($key))
                    <x-form-error message="{{ $errors->first($key) }}" />
                @endif
            @endforeach
        @else
            @if ($errors->has($error))
                <x-form-error message="{{ $errors->first($error) }}" />
            @endif
        @endif
    @endif
</div>
