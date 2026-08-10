@props([
    'label' => null,
    'description' => '',
    'icon' => null,
    'required' => false,
    'optional' => false,
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
    <div class="relative">
        <select
            {{ $attributes->except('label', 'description', 'icon', 'required', 'optional', 'error')->merge([
                'class' =>
                    'w-full bg-white/10 border-2 border-white/25 rounded-2xl p-4 text-xl md:text-2xl text-white placeholder:text-white/50 focus:outline-none focus:border-highlight focus:ring-2 focus:ring-highlight/40 transition-colors appearance-none bg-no-repeat pr-12',
            ]) }}
        >
            {{ $slot }}
        </select>
        <i
            class='bx bxs-chevron-down pointer-events-none absolute right-5 top-1/2 -translate-y-1/2 text-3xl text-white/50'></i>
    </div>
</x-form-field>
