<?php

namespace App\Filament\Resources\ColonyResource\Pages;

use App\Filament\Resources\ColonyResource;
use App\Imports\ColoniesImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ListColonies extends ListRecords
{
    protected static string $resource = ColonyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('importColonies')
                ->label('Importar Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalDescription('El archivo Excel debe tener encabezados en la primera fila. Las columnas requeridas son: "ciudad" y "colonia".')
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo Excel')
                        ->required()
                        ->helperText('Asegúrese de que el archivo cumpla con el formato solicitado.')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ]),
                ])
                ->action(function (array $data) {
                    try {
                        Excel::import(
                            new ColoniesImport,
                            $data['file'],
                            'public'
                        );

                        Notification::make()
                            ->title('Colonias importadas')
                            ->success()
                            ->body('El archivo fue procesado correctamente.')
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Error al importar')
                            ->danger()
                            ->body('El archivo no tiene el formato correcto o contiene datos inválidos. Por favor verifique que las columnas "ciudad" y "colonia" existan.')
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }
}
