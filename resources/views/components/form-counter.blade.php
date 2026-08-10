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
    <div class="flex items-center gap-6 max-w-sm">
        <button type="button" wire:click="$set('{{ $field }}', {{ max($min, $value - 1) }})" class="w-16 h-16 rounded-2xl bg-white/10 border-2 border-white/25 text-white text-3xl font-bold hover:bg-white/20 flex items-center justify-center">-</button>
        <input type="text" inputmode="numeric" readonly value="{{ $value }}" class="w-24 bg-white/10 border-2 border-white/25 rounded-2xl p-4 text-3xl text-center font-bold text-white">
        <button type="button" wire:click="$set('{{ $field }}', {{ min($max, $value + 1) }})" class="w-16 h-16 rounded-2xl bg-white/10 border-2 border-white/25 text-white text-3xl font-bold hover:bg-white/20 flex items-center justify-center">+</button>
    </div>
</x-form-field>
