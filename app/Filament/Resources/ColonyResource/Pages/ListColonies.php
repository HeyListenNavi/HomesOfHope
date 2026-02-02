<?php

namespace App\Filament\Resources\ColonyResource\Pages;

use App\Filament\Resources\ColonyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Imports\ColoniesImport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;


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
                    \Filament\Forms\Components\FileUpload::make('file')
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
