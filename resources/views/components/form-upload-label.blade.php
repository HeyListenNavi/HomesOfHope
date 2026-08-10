@props([
    'icon' => 'bxs-file',
    'iconClass' => 'text-sky-300 text-7xl',
    'bgClass' => 'bg-sky-400/10 hover:bg-sky-400/20',
    'borderClass' => 'border-sky-300/50',
    'text' => 'Subir archivo',
    'textClass' => 'text-3xl font-bold text-white',
    'paddingClass' => 'px-6 py-12',
])

<label
    class="flex cursor-pointer flex-col items-center justify-center gap-4 rounded-2xl border-2 border-dashed {{ $borderClass }} {{ $bgClass }} {{ $paddingClass }} text-center transition-colors"
>
    <i class='bx {{ $icon }} {{ $iconClass }}'></i>
    <span class="{{ $textClass }}">{{ $text }}</span>
    <input
        type="file"
        class="sr-only"
        {{ $attributes->except('icon', 'iconClass', 'bgClass', 'borderClass', 'text', 'textClass', 'paddingClass') }}
    />
</label>
