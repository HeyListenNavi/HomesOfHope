@props([
    'label' => null,
    'description' => '',
    'required' => false,
    'optional' => false,
    'successText' => 'Documento cargado con éxito.',
    'uploadText' => 'Subir archivo',
    'icon' => 'bxs-file',
    'colorClass' => 'text-emerald-300',
    'bgClass' => 'bg-emerald-400/10',
    'borderClass' => 'border-emerald-300/50',
    'success' => false,
    'error' => '',
])

<x-form-field
    :label="$label"
    :description="$description"
    :icon="$icon"
    :required="$required"
    :optional="$optional"
    :error="$error"
    class="space-y-4 rounded-3xl border-2 border-white/20 bg-white/10 p-6 md:p-8"
>
    @if ($success)
        <div class="bg-highlight-500/20 border-highlight-400 rounded-xl border-2 p-6 text-center">
            <i class='bx bxs-check-circle text-highlight-300 mb-2 text-6xl'></i>
            <span class="block text-2xl font-bold text-white">{{ $successText }}</span>
            <button
                class="mt-4 text-xl font-bold text-white/70 underline hover:text-white"
                type="button"
            >Quitar y cambiar</button>
        </div>
    @else
        <label
            class="{{ $bgClass }} {{ $borderClass }} flex cursor-pointer flex-col items-center justify-center gap-4 rounded-2xl border-2 border-dashed px-6 py-12 text-center transition-colors hover:bg-opacity-20"
        >
            <i class='bx {{ $icon }} {{ $colorClass }} text-7xl'></i>
            <span class="text-3xl font-bold text-white">{{ $uploadText }}</span>
            <input
                class="sr-only"
                {{ $attributes->except('label', 'description', 'required', 'optional', 'successText', 'uploadText', 'icon', 'colorClass', 'bgClass', 'borderClass', 'success', 'error') }}
            />
        </label>
    @endif
</x-form-field>
