@props([
    'label' => null,
    'description' => '',
    'icon' => null,
    'required' => false,
    'optional' => false,
    'rows' => 3,
    'placeholder' => '',
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
    <textarea
        rows="{{ $rows }}"
        {{ $attributes->except('label', 'description', 'icon', 'required', 'optional', 'rows', 'placeholder', 'error')->merge([
            'class' =>
                'w-full bg-white/10 border-2 border-white/25 rounded-2xl p-4 text-xl md:text-2xl text-white placeholder:text-white/50 focus:outline-none focus:border-highlight focus:ring-2 focus:ring-highlight/40 transition-colors',
            'placeholder' => $placeholder,
        ]) }}
    >{{ $slot }}</textarea>
</x-form-field>
