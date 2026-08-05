<x-filament-panels::page>
    <div
        class="flex flex-col gap-4"
        x-data="visitMapData({
            markersData: @js($this->markers),
            iconHome: @js(svg('heroicon-s-home')->toHtml()),
            iconLand: @js(svg('heroicon-s-map')->toHtml())
        })"
        x-on:sync-map-points.window="drawMapPoints($event.detail.markers)"
        x-on:sync-selected-locations.window="syncSelectedColors()"
    >

        <x-filament::section
            class="mb-6"
            style="position: relative; z-index: 10;"
        >
            {{ $this->filtersForm }}
        </x-filament::section>

        <x-filament::grid
            class="items-start gap-6"
            default="1"
            lg="6"
        >

            <x-filament::grid.column lg="4">
                <x-filament::section class="map-section-wrapper h-full overflow-hidden">
                    <div
                        style="width: 100%; height: 70vh; min-height: 600px; position: relative;"
                        wire:ignore
                    >
                        <div
                            id="map"
                            style="width: 100%; height: 100%; z-index: 1;"
                        ></div>
                    </div>
                </x-filament::section>
            </x-filament::grid.column>

            <x-filament::grid.column lg="2">
                <div class="flex flex-col gap-6">
                    <x-filament::section
                        heading="Ruta de Visitas"
                        description="Se generaran las visitas de cada familia seleccionada"
                    >
                        <div class="flex flex-col gap-4">
                            @if (count($this->selectedLocations) > 0)
                                <div
                                    class="mb-6 flex flex-col gap-3 overflow-y-auto pr-2"
                                    style="max-height: 40vh;"
                                >
                                    @foreach ($this->selectedDetails as $detail)
                                        <div
                                            class="flex items-center justify-between rounded-xl border border-gray-200 p-3 shadow-sm dark:border-white/10 dark:bg-gray-800">
                                            <div class="flex items-center gap-3">
                                                <div class="flex-shrink-0">
                                                    @if ($detail['type'] === 'home')
                                                        <x-filament::icon
                                                            class="h-6 w-6"
                                                            style="color: rgb(var(--primary-600));"
                                                            icon="heroicon-s-home"
                                                        />
                                                    @else
                                                        <x-filament::icon
                                                            class="h-6 w-6"
                                                            style="color: rgb(var(--success-600));"
                                                            icon="heroicon-s-map"
                                                        />
                                                    @endif
                                                </div>
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-sm font-bold leading-tight text-gray-900 dark:text-white"
                                                    >{{ $detail['family_name'] }}</span>
                                                    <span
                                                        class="mt-0.5 text-xs font-semibold text-gray-500 dark:text-gray-400"
                                                    >{{ $detail['label'] }}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <x-filament::icon-button
                                                    href="{{ $detail['view_url'] }}"
                                                    title="Abrir Perfil Completo"
                                                    icon="heroicon-m-arrow-top-right-on-square"
                                                    color="gray"
                                                    size="sm"
                                                    tag="a"
                                                    target="_blank"
                                                />
                                                <x-filament::icon-button
                                                    title="Remover de la Ruta"
                                                    icon="heroicon-m-x-mark"
                                                    color="danger"
                                                    size="sm"
                                                    wire:click="removeLocation({{ $detail['family_id'] }}, '{{ $detail['type'] }}')"
                                                />
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div
                                    class="flex flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-gray-400 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-500">
                                    <x-filament::icon
                                        class="mb-3 h-10 w-10 text-gray-300 dark:text-gray-600"
                                        icon="heroicon-s-map-pin"
                                    />
                                    <div class="flex flex-col gap-1">
                                        <p class="text-sm font-medium">No hay ubicaciones seleccionadas</p>
                                        <p class="text-xs">Haz clic en los marcadores del mapa para agregarlos a tu
                                            ruta.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div class="flex flex-col gap-6">
                                {{ $this->form }}

                                <div>
                                    {{ $this->finalizeAction }}
                                </div>
                            </div>
                        </div>
                    </x-filament::section>
                </div>
            </x-filament::grid.column>
        </x-filament::grid>
    </div>
</x-filament-panels::page>
