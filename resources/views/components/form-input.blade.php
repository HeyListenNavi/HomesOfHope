@props(['label' => '', 'id', 'name' => $id, 'placeholder' => '', 'required' => false, 'type' => 'text'])

<fieldset class="{{ in_array($type, ['checkbox', 'radio']) ? 'flex items-center gap-2' : 'flex flex-col gap-4' }}">
    @if (in_array($type, ['checkbox', 'radio']))
        <label for="{{ $id }}"
            class="text-body-small relative flex w-full cursor-pointer items-center gap-4 rounded-2xl border-2 border-white/25 bg-white/10 p-5 transition-all hover:border-white/60 hover:bg-white/20 focus-within:ring-2 focus-within:ring-white/60 has-checked:border-highlight has-checked:bg-highlight has-checked:font-bold">
            <input
                {{ $attributes->merge([
                    'class' => 'peer sr-only',
                    'type' => $type,
                    'name' => $name,
                    'id' => $id,
                    'value' => $label,
                    'required' => $required,
                ]) }} />
            <span
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border-2 bg-white text-zinc-300 transition-all hover:border-highlight hover:text-highlight peer-checked:border-white peer-checked:bg-white peer-checked:text-highlight peer-checked:shadow-md">
                <i class="bx bx-check text-base"></i>
            </span>
            <span class="min-w-0 flex-1">{{ $slot }}</span>
        </label>
    @elseif ($type === 'textarea')
        <label for="{{ $id }}" class="text-body-small">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
        <textarea
            {{ $attributes->merge([
                'class' =>
                    'w-full text-label-medium leading-6 bg-background-light text-foreground-tertiary rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-highlight min-h-[180px] resize-none',
                'name' => $name,
                'id' => $id,
                'placeholder' => $placeholder,
                'required' => $required,
            ]) }}>{{ $slot }}</textarea>
    @else
        <label for="{{ $id }}" class="text-body-small">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
        <input
            {{ $attributes->merge([
                'class' =>
                    'w-full text-label-medium leading-6 bg-background-light text-foreground-tertiary rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-highlight',
                'type' => $type,
                'name' => $name,
                'id' => $id,
                'placeholder' => $placeholder,
                'required' => $required,
            ]) }} />
    @endif
</fieldset>
