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

        <div class="flex items-center gap-1">
            <button type="button" wire:click="previous" class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 transition-colors">
                <x-filament::icon icon="heroicon-m-chevron-left" class="w-5 h-5" />
            </button>

            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 min-w-24 text-center px-1">
                {{ $this->getPeriodLabel() }}
            </span>

            <button type="button" wire:click="next" @disabled($this->isAtCurrentPeriod()) @class([
                'flex items-center justify-center w-8 h-8 rounded-full transition-colors',
                'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400' => !$this->isAtCurrentPeriod(),
                'opacity-50 cursor-not-allowed text-gray-400 dark:text-gray-600' => $this->isAtCurrentPeriod(),
            ])>
                <x-filament::icon icon="heroicon-m-chevron-right" class="w-5 h-5" />
            </button>

            <x-filament::input.wrapper
                class="w-max sm:-my-2 ml-1"
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
