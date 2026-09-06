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
            class="bg-highlight/20 border-highlight flex flex-col items-center gap-4 rounded-2xl border-2 p-6 md:p-8 text-center shadow-lg animate-in fade-in zoom-in duration-300">
            <div class="w-20 h-20 rounded-full bg-highlight/30 flex items-center justify-center border-2 border-highlight">
                <i class='bx bxs-check-circle text-highlight text-6xl'></i>
            </div>
            <span class="block text-2xl md:text-3xl font-black text-white">{{ $successText }}</span>
            <button
                class="flex items-center justify-center gap-2 rounded-2xl bg-white/15 hover:bg-white/25 border border-white/20 px-8 py-3.5 text-xl font-bold text-white transition-all active:scale-95 cursor-pointer shadow-md"
                type="button"
                @if ($removeAction) wire:click="{{ $removeAction }}" @endif
            >
                <i class='bx bx-refresh text-2xl'></i>
                {{ $removeLabel }}
            </button>
        </div>
    @else
        {{ $slot }}
    @endif
</x-form-field>
