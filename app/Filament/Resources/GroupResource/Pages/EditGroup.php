<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Barryvdh\DomPDF\Facade\Pdf;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('exportPdf')
                ->label('Exportar (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function ($record) {
                    $record->load('applicants');

                    $pdf = Pdf::loadView('exports.group-export', ['group' => $record])
                        ->setOption(['defaultFont' => 'sans-serif'])
                        ->setOption(['isHtml5ParserEnabled' => true]);

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->stream();
                    }, $record->name . '.pdf');
                }),
        ];
    }
}
