@props([
    'label' => 'Últimos Recibos (Hasta 5)',
    'description' => '',
    'icon' => null,
    'required' => false,
    'optional' => false,
    'receipts' => [],
    'max' => 5,
    'model' => null,
    'error' => [],
])

<x-form-field
    :label="$label"
    :description="$description"
    :icon="$icon"
    :required="$required"
    :optional="$optional"
    :error="$error"
    class="flex flex-col gap-4 rounded-3xl border-2 border-white/20 bg-white/10 p-6 md:p-8"
>
    @if (count($receipts) > 0)
        <div class="flex flex-col gap-3">
            @foreach ($receipts as $idx => $receipt)
                <div class="flex items-center gap-4 rounded-2xl border-2 border-white/20 bg-white/10 p-4">
                    <i
                        class='bx {{ str_contains($receipt->getMimeType(), 'pdf') ? 'bxs-file-pdf text-red-300' : 'bxs-image text-sky-300' }} shrink-0 text-4xl'></i>
                    <span
                        class="flex-1 truncate text-xl font-bold text-white">{{ Str::limit($receipt->getClientOriginalName(), 30) }}</span>
                    <button
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-500/20 text-red-300 transition-colors hover:bg-red-500 hover:text-white"
                        type="button"
                        aria-label="Eliminar recibo"
                        wire:click="removeLandReceipt({{ $idx }})"
                    >
                        <i class='bx bx-trash text-2xl'></i>
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    @if (count($receipts) < $max)
        <label
            class="flex cursor-pointer flex-col items-center justify-center gap-4 rounded-2xl border-2 border-dashed border-emerald-300/50 bg-emerald-400/10 px-6 py-10 text-center transition-colors hover:bg-emerald-400/20"
        >
            <i class='bx bxs-receipt text-7xl text-emerald-300'></i>
            <span class="text-3xl font-bold text-white">+ Agregar un recibo</span>
            <span class="text-xl text-white/70">({{ count($receipts) }} de {{ $max }})</span>
            <input
                class="sr-only"
                type="file"
                multiple
                accept="image/*,.pdf"
                @if ($model) wire:model="{{ $model }}" @endif
            />
        </label>
    @else
        <div
            class="flex flex-col items-center gap-2 rounded-2xl border-2 border-amber-400/40 bg-amber-500/15 p-4 text-center">
            <i class='bx bxs-check-circle text-4xl text-amber-300'></i>
            <p class="text-xl font-bold text-amber-200">Has alcanzado el máximo de {{ $max }} recibos.</p>
            <p class="text-base text-amber-100/80">Elimina alguno para agregar más.</p>
        </div>
    @endif

    <x-form-upload-loading
        text="Subiendo recibo..."
        @if ($model) wire:target="{{ $model }}" @endif
    />
</x-form-field>
