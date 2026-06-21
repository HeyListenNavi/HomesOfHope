<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        wire:ignore
        class="w-full space-y-4"
        x-data="groupApplicantMap({
            points: @js($getMapData()['points']),
            unmappable: @js($getMapData()['unmappable']),
            center: @js($getCenter()),
            zoom: @js($getZoom())
        })"
    >
        <div
            class="w-full overflow-hidden rounded-lg border border-gray-300 shadow-sm dark:border-gray-600"
            style="height: {{ $getHeight() }}px; min-height: 400px; z-index: 0;"
            x-ref="map"
        ></div>

        <template x-if="unmappable.length > 0">
            <div class="mt-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Aplicantes sin ubicación mapeable (<span x-text="unmappable.length"></span>)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    <template x-for="applicant in unmappable" :key="applicant.id">
                        <div class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm flex flex-col justify-between">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white" x-text="applicant.name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="applicant.city"></p>
                            </div>
                            <div class="mt-2 flex gap-2">
                                <template x-if="applicant.url">
                                    <a :href="applicant.url" target="_blank" rel="noopener noreferrer" class="text-xs font-medium text-primary-600 hover:underline">
                                        Ver Perfil
                                    </a>
                                </template>
                                <template x-if="applicant.map_url">
                                    <a :href="applicant.map_url" target="_blank" rel="noopener noreferrer" class="text-xs font-medium text-gray-600 hover:underline flex items-center gap-1">
                                        <x-heroicon-m-map-pin class="w-3 h-3" />
                                        Ver en Mapa
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>