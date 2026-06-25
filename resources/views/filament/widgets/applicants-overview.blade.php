@php
    $columns = $this->getColumns();
    $heading = $this->getHeading();
    $description = $this->getDescription();
    $hasHeading = filled($heading);
    $hasDescription = filled($description);
    $filters = $this->getFilters();
@endphp

<x-filament-widgets::widget class="fi-wi-stats-overview grid gap-y-4">
    <div class="fi-wi-stats-overview-header flex items-center justify-between gap-4">
        <div class="grid gap-y-1">
            <h3
                class="fi-wi-stats-overview-header-heading col-span-full text-base font-semibold leading-6 text-gray-950 dark:text-white">
                {{ $heading }}
            </h3>
            <p
                class="fi-wi-stats-overview-header-description overflow-hidden break-words text-sm text-gray-500 dark:text-gray-400">
                {{ $description }}
            </p>
        </div>

        <x-filament::input.wrapper
            class="w-max sm:-my-2"
            inline-prefix
            wire:target="filter"
        >
            <x-filament::input.select
                inline-prefix
                wire:model.live="filter"
            >
                @foreach ($filters as $value => $label)
                    <option value="{{ $value }}">
                        {{ $label }}
                    </option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    <div @class([
        'fi-wi-stats-overview-stats-ctn grid gap-6',
        'md:grid-cols-1' => $columns === 1,
        'md:grid-cols-2' => $columns === 2,
        'md:grid-cols-3' => $columns === 3,
        'md:grid-cols-2 xl:grid-cols-4' => $columns === 4,
    ])>
        @foreach ($this->getCachedStats() as $stat)
            {{ $stat }}
        @endforeach
    </div>
</x-filament-widgets::widget>
