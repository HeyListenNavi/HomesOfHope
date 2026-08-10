@props([
    'label' => null,
    'description' => '',
    'icon' => null,
    'required' => false,
    'optional' => false,
    'error' => '',
    'disabled' => false,
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
        <div
            {{ $attributes->merge([
                'class' => 'grid grid-cols-3 gap-4',
                'x-data' => 'datePicker',
                'x-modelable' => 'value',
            ]) }}>
            <x-form-select
                x-model="day"
                :disabled="$disabled"
            >
                <option
                    class="font-bold text-black"
                    value=""
                >Día</option>
                <template
                    x-for="day in days()"
                    :key="day"
                >
                    <option
                        class="text-slate-800"
                        :value="day.toString()"
                        x-text="day"
                    ></option>
                </template>
            </x-form-select>

            <x-form-select
                x-model="month"
                :disabled="$disabled"
            >
                <option
                    class="font-bold text-black"
                    value=""
                >Mes</option>
                <template
                    x-for="month in months"
                    :key="month.val"
                >
                    <option
                        class="text-slate-800"
                        :value="month.val"
                        x-text="month.name"
                    ></option>
                </template>
            </x-form-select>

            <x-form-select
                x-model="year"
                :disabled="$disabled"
            >
                <option
                    class="font-bold text-black"
                    value=""
                >Año</option>
                <template
                    x-for="year in years()"
                    :key="year"
                >
                    <option
                        class="text-slate-800"
                        :value="year.toString()"
                        x-text="year"
                    ></option>
                </template>
            </x-form-select>
        </div>
    </div>
</x-form-field>
