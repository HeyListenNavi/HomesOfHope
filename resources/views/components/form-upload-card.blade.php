@props([
    'title' => null,
    'description' => '',
    'icon' => null,
    'required' => false,
    'optional' => false,
    'success' => false,
    'successText' => 'Documento cargado con éxito.',
    'removeLabel' => 'Quitar y cambiar',
    'removeAction' => null,
    'error' => [],
])

<x-form-field
    :label="$title"
    :description="$description"
    :icon="$icon"
    :required="$required"
    :optional="$optional"
    :error="$error"
    class="flex flex-col gap-4 rounded-3xl border-2 border-white/20 bg-white/10 p-6 md:p-8"
>
    @if ($success)
        <div
            class="bg-highlight/20 border-highlight flex flex-col items-center gap-2 rounded-xl border-2 p-6 text-center">
            <i class='bx bxs-check-circle text-highlight text-6xl'></i>
            <span class="block text-2xl font-bold text-white">{{ $successText }}</span>
            <button
                class="text-xl font-bold text-white/70 underline hover:text-white"
                type="button"
                @if ($removeAction) wire:click="{{ $removeAction }}" @endif
            >{{ $removeLabel }}</button>
        </div>
    @else
        {{ $slot }}
    @endif
</x-form-field>
