<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use App\Exports\GroupApplicantsExport;
use Maatwebsite\Excel\Facades\Excel;
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

            // --- ACCIÓN EXPORTAR PDF (EXISTENTE) ---
            Actions\Action::make('exportPdf')
                ->label('PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger') // Color rojo para PDF
                ->action(function ($record) {
                    $record->load('applicants');
                    $pdf = Pdf::loadView('exports.group-export', ['group' => $record])
                        ->setOption(['defaultFont' => 'sans-serif'])
                        ->setOption(['isHtml5ParserEnabled' => true]);

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->stream();
                    }, $record->name . '.pdf');
                }),

            // --- ACCIÓN EXPORTAR EXCEL (NUEVA) ---
            Actions\Action::make('exportExcel')
                ->label('Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success') // Color verde para Excel
                ->action(function ($record) {
                    // Genera y descarga el Excel usando la clase que creamos
                    return Excel::download(
                        new GroupApplicantsExport($record),
                        "Grupo - {$record->name}.xlsx"
                    );
                }),
        ];
    }
}
