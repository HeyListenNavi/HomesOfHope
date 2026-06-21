<?php

namespace App\Filament\Resources\ColonyResource\Pages;

use App\Filament\Resources\ColonyResource;
use App\Imports\ColoniesImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

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
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo Excel')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ]),
                ])
                ->action(function (array $data) {

                    Excel::import(
                        new ColoniesImport,
                        $data['file']
                    );

                    Notification::make()
                        ->title('Colonias importadas')
                        ->success()
                        ->body('El archivo fue procesado correctamente.')
                        ->send();
                }),
        ];
    }
}
