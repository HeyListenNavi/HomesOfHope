@props([
    'label' => null,
    'description' => '',
    'icon' => null,
    'required' => false,
    'optional' => false,
    'yesLabel' => 'Sí',
    'noLabel' => 'No',
    'yesIcon' => 'bxs-check-circle',
    'noIcon' => 'bxs-x-circle',
    'yesActive' => false,
    'noActive' => false,
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
    <div class="grid grid-cols-1 gap-4">
        <button type="button" @if($attributes->has('wire-click-yes')) wire:click="{{ $attributes->get('wire-click-yes') }}" @endif class="flex items-center gap-6 p-6 rounded-2xl border-4 transition-all {{ $yesActive ? 'border-highlight bg-highlight/20' : 'border-white/25 bg-white/10 hover:bg-white/20' }}">
            <i class='bx {{ $yesIcon }} text-5xl {{ $yesActive ? 'text-highlight' : 'text-white/40' }}'></i>
            <span class="text-2xl font-bold text-left {{ $yesActive ? 'text-white' : 'text-white/70' }}">{{ $yesLabel }}</span>
        </button>
        <button type="button" @if($attributes->has('wire-click-no')) wire:click="{{ $attributes->get('wire-click-no') }}" @endif class="flex items-center gap-6 p-6 rounded-2xl border-4 transition-all {{ $noActive ? 'border-amber-400 bg-amber-500/20' : 'border-white/25 bg-white/10 hover:bg-white/20' }}">
            <i class='bx {{ $noIcon }} text-5xl {{ $noActive ? 'text-amber-400' : 'text-white/40' }}'></i>
            <span class="text-2xl font-bold text-left {{ $noActive ? 'text-white' : 'text-white/70' }}">{{ $noLabel }}</span>
        </button>
    </div>
</x-form-field>
