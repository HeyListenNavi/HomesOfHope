@props([
    'label' => null,
    'description' => '',
    'icon' => null,
    'required' => false,
    'optional' => false,
    'value' => 1,
    'min' => 1,
    'max' => 20,
    'field' => 'count',
    'error' => '',
])

<x-form-field
    :label="$label"
    :description="$description"
    :icon="$icon"
    :required="$required"
    :optional="$optional"
    :error="$error"
    class="space-y-6 pt-8 border-t border-white/15"
>
    <div class="flex items-center gap-4 sm:gap-6 max-w-md">
        <button
            type="button"
            wire:click="$set('{{ $field }}', {{ max($min, $value - 1) }})"
            class="w-18 h-18 sm:w-20 sm:h-20 rounded-2xl bg-white/15 border-2 border-white/30 text-white text-4xl font-black hover:bg-white/25 active:scale-95 transition-all flex items-center justify-center cursor-pointer shadow-lg select-none"
            aria-label="Restar una persona"
        >−</button>
        <div class="flex-1 flex flex-col items-center justify-center bg-white/10 border-2 border-white/30 rounded-2xl py-3 px-4 shadow-inner">
            <span class="text-4xl sm:text-5xl font-black text-white leading-none">{{ $value }}</span>
            <span class="text-base sm:text-lg font-bold text-white/80 mt-1">{{ $value == 1 ? 'persona' : 'personas' }}</span>
        </div>
        <button
            type="button"
            wire:click="$set('{{ $field }}', {{ min($max, $value + 1) }})"
            class="w-18 h-18 sm:w-20 sm:h-20 rounded-2xl bg-highlight/80 hover:bg-highlight border-2 border-highlight text-white text-4xl font-black active:scale-95 transition-all flex items-center justify-center cursor-pointer shadow-lg select-none"
            aria-label="Sumar una persona"
        >+</button>
    </div>
</x-form-field>
