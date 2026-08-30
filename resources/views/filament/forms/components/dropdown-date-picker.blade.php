@php
    $extraAlpineAttributes = $getExtraAlpineAttributes();
    $id = $getId();
    $isDisabled = $isDisabled();
    $isReadOnly = $isReadOnly();
    $isInteractive = ! $isDisabled && ! $isReadOnly;
    $statePath = $getStatePath();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="dropdownDatePickerComponent({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            showAge: @js($shouldShowAge()),
            disabled: @js($isDisabled),
            readonly: @js($isReadOnly),
        })"
        class="w-full space-y-2"
    >
        <div
            class="grid grid-cols-3 gap-2 w-full"
            style="display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)) !important; gap: 0.5rem !important; width: 100% !important;"
        >
            <div class="w-full min-w-0">
                <x-filament::input.wrapper :disabled="! $isInteractive" class="w-full">
                    <x-filament::input.select
                        x-model="selectedDay"
                        x-on:change="onPartChange()"
                        :disabled="! $isInteractive"
                        class="w-full"
                    >
                        <option value="">{{ __('Día') }}</option>
                        @for ($d = 1; $d <= 31; $d++)
                            @php $val = str_pad((string) $d, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $val }}" x-show="{{ $d }} <= daysInMonth">{{ $d }}</option>
                        @endfor
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="w-full min-w-0">
                <x-filament::input.wrapper :disabled="! $isInteractive" class="w-full">
                    <x-filament::input.select
                        x-model="selectedMonth"
                        x-on:change="onPartChange()"
                        :disabled="! $isInteractive"
                        class="w-full"
                    >
                        <option value="">{{ __('Mes') }}</option>
                        @foreach($getMonths() as $monthKey => $monthName)
                            <option value="{{ $monthKey }}">{{ $monthName }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="w-full min-w-0">
                <x-filament::input.wrapper :disabled="! $isInteractive" class="w-full">
                    <x-filament::input.select
                        x-model="selectedYear"
                        x-on:change="onPartChange()"
                        :disabled="! $isInteractive"
                        class="w-full"
                    >
                        <option value="">{{ __('Año') }}</option>
                        @foreach($getYears() as $yearKey => $yearLabel)
                            <option value="{{ $yearKey }}">{{ $yearLabel }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>

        <template x-if="showAge && calculatedAge !== null">
            <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 font-medium">                
                <span>Edad:</span>
                <span class="font-bold" x-text="calculatedAge"></span>
                <span>años</span>
            </div>
        </template>
    </div>
</x-dynamic-component>
