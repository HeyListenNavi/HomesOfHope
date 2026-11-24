@php
    $previousRecordId = $previousRecordId ?? null;
    $nextRecordId = $nextRecordId ?? null;
    $currentPosition = $currentPosition ?? null;
    $totalCount = $totalCount ?? 0;
@endphp

<div
    class="space-y-4"
    @if($totalCount > 1)
        x-data
        @keydown.window.arrow-left="if (!['INPUT', 'TEXTAREA', 'SELECT'].includes($event.target.tagName) && @js($previousRecordId !== null)) { $event.preventDefault(); $wire.replaceMountedTableAction('preview', @js($previousRecordId)); }"
        @keydown.window.arrow-right="if (!['INPUT', 'TEXTAREA', 'SELECT'].includes($event.target.tagName) && @js($nextRecordId !== null)) { $event.preventDefault(); $wire.replaceMountedTableAction('preview', @js($nextRecordId)); }"
    @endif
>
    @if($totalCount > 1)
        <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-900">
            <button
                type="button"
                @if($previousRecordId !== null)
                    wire:click="replaceMountedTableAction('preview', '{{ $previousRecordId }}')"
                @else
                    disabled
                @endif
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                <x-heroicon-m-chevron-left class="h-4 w-4" />
                <span>Anterior</span>
            </button>

            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ $currentPosition }} / {{ $totalCount }}
            </span>

            <button
                type="button"
                @if($nextRecordId !== null)
                    wire:click="replaceMountedTableAction('preview', '{{ $nextRecordId }}')"
                @else
                    disabled
                @endif
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                <span>Siguiente</span>
                <x-heroicon-m-chevron-right class="h-4 w-4" />
            </button>
        </div>
    @endif

    @switch($record->preview_type)
        @case(\App\Enums\FilePreviewType::Image)
            <div
                class="flex items-center justify-center overflow-hidden rounded-xl bg-gray-50 p-2 dark:bg-gray-900"
                style="height: 70vh"
            >
                <img
                    class="h-full max-w-full rounded-lg object-contain shadow-sm"
                    src="{{ $url }}"
                    alt="{{ $record->original_name }}"
                />
            </div>
        @break

        @case(\App\Enums\FilePreviewType::Pdf)
            <div
                class="w-full overflow-hidden rounded-xl border border-gray-200 bg-gray-100 shadow-inner dark:border-gray-700 dark:bg-gray-900"
                style="height: 70vh"
            >
                <iframe
                    class="mx-auto h-full w-full max-w-2xl border-0"
                    src="{{ $url }}#toolbar=1"
                    title="{{ $record->original_name }}"
                ></iframe>
            </div>
        @break

        @case(\App\Enums\FilePreviewType::Video)
            <div
                class="flex w-full overflow-hidden rounded-xl border border-gray-200 bg-gray-100 shadow-inner dark:border-gray-700 dark:bg-gray-900"
                style="height: 70vh"
            >
                <video
                    class="mx-auto h-auto w-full rounded-lg object-contain shadow-sm"
                    src="{{ $url }}"
                    controls
                >
                    Tu navegador no soporta la reproducción de video.
                </video>
            </div>
        @break

        @case(\App\Enums\FilePreviewType::Audio)
            <div
                class="flex flex-col items-center justify-center space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-12 text-center dark:border-gray-700 dark:bg-gray-900">
                <div class="py-12">
                    <div class="bg-success-50 dark:bg-success-950/40 text-success-600 dark:text-success-400 rounded-full p-4">
                        <x-heroicon-o-speaker-wave class="mx-auto h-16 w-16" />
                    </div>
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $record->original_name }}</h4>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $record->mime_type ?? 'Audio' }} •
                            {{ $record->size ? number_format($record->size / 1024, 2) . ' KB' : 'N/A' }}
                        </p>
                    </div>
                </div>
                <audio
                    class="w-full pt-2"
                    src="{{ $url }}"
                    controls
                >
                    Tu navegador no soporta la reproducción de audio.
                </audio>
            </div>
        @break

        @default
            <div
                class="flex flex-col items-center justify-center space-y-4 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-12 text-center dark:border-gray-700 dark:bg-gray-900">
                <div class="py-12">
                    <div class="bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 rounded-full p-4">
                        <x-heroicon-o-document-text class="mx-auto h-16 w-16" />
                    </div>
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $record->original_name }}</h4>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $record->mime_type ?? 'Archivo' }} •
                            {{ $record->size ? number_format($record->size / 1024, 2) . ' KB' : 'N/A' }}
                        </p>
                    </div>
                    <p class="max-w-md text-sm text-gray-600 dark:text-gray-300">
                        Este tipo de archivo no cuenta con previsualización embebida directa. Puedes descargarlo o abrirlo en
                        una nueva pestaña usando los botones inferiores.
                    </p>
                </div>
            </div>
    @endswitch
</div>
