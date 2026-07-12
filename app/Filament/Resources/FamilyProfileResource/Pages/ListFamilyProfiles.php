<?php

namespace App\Filament\Resources\FamilyProfileResource\Pages;

use App\Exports\FamilyProfileTemplateExport;
use App\Filament\Resources\FamilyProfileResource;
use App\Imports\FamilyProfileImport;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListFamilyProfiles extends ListRecords
{
    protected static string $resource = FamilyProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('download_template')
                    ->label('Descargar Plantilla')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(fn () => Excel::download(new FamilyProfileTemplateExport, 'plantilla-familias.xlsx')),

                Actions\Action::make('import_data')
                    ->label('Importar Datos')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->modalDescription('Sube el archivo Excel con los datos. Consulta la pestaña "Guía de Valores" en la plantilla para ver los formatos aceptados.')
                    ->form([
                        Forms\Components\FileUpload::make('attachment')
                            ->label('Archivo Excel')
                            ->disk('local')
                            ->directory('imports')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $file = Storage::disk('local')->path($data['attachment']);

                        try {
                            Excel::import(new FamilyProfileImport, $file);

                            Notification::make()
                                ->title('Importación finalizada con éxito')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error durante la importación')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
                ->label('Importar')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('gray')
                ->outlined(),

            Actions\CreateAction::make(),
        ];
    }
}
