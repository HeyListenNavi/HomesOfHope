@props([
    'label' => null,
    'description' => '',
    'icon' => null,
    'required' => false,
    'optional' => false,
    'model' => null,
    'options' => [],
    'enum' => null,
    'error' => '',
])

<x-form-field
    :label="$label"
    :description="$description"
    :icon="$icon"
    :required="$required"
    :optional="$optional"
    :error="$error"
>
    <div class="grid grid-cols-1 gap-3">
        @if ($enum)
            @foreach ($enum::cases() as $case)
                <label class="flex cursor-pointer items-center gap-4 rounded-2xl border-2 border-white/25 bg-white/10 p-5">
                    <input
                        type="checkbox"
                        value="{{ $case->value }}"
                        class="accent-highlight h-7 w-7 shrink-0"
                        @if ($model) wire:model="{{ $model }}" @endif
                    >
                    <span class="text-2xl font-bold text-white">{{ $case->getLabel() }}</span>
                </label>
            @endforeach
        @else
            @foreach ($options as $value => $optionLabel)
                <label class="flex cursor-pointer items-center gap-4 rounded-2xl border-2 border-white/25 bg-white/10 p-5">
                    <input
                        type="checkbox"
                        value="{{ $value }}"
                        class="accent-highlight h-7 w-7 shrink-0"
                        @if ($model) wire:model="{{ $model }}" @endif
                    >
                    <span class="text-2xl font-bold text-white">{{ $optionLabel }}</span>
                </label>
            @endforeach
        @endif
    </div>
</x-form-field>
