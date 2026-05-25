<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use App\Exports\GroupApplicantsExport;
use App\Models\Group;
use App\Services\Group\GroupService;
use Filament\Forms;
use Filament\Notifications\Notification;
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

            // --- ACCIÓN REENVIAR INFORMACIÓN ---
            Actions\Action::make('resendInfo')
                ->label('Reenviar Información')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Reenviar información de entrevista')
                ->modalDescription('¿Estás seguro de que deseas reenviar la información de la entrevista a todos los miembros de este grupo?')
                ->action(function (Group $record, GroupService $groupService) {
                    $groupService->reSendGroupMessage($record);

                    Notification::make()
                        ->title('Información enviada')
                        ->body("Se ha enviado la información a los miembros del grupo.")
                        ->success()
                        ->send();
                }),

            // --- ACCIÓN ENVIAR AVISO ---
            Actions\Action::make('sendAnnouncement')
                ->label('Enviar Aviso')
                ->icon('heroicon-o-megaphone')
                ->color('warning')
                ->form([
                    Forms\Components\Textarea::make('announcement')
                        ->label('Aviso')
                        ->placeholder('Escribe aquí el aviso para los miembros...')
                        ->required()
                        ->rows(5),
                ])
                ->action(function (Group $record, array $data, GroupService $groupService) {
                    $groupService->sendCustomMessageToGroup($record, $data['announcement']);

                    Notification::make()
                        ->title('Aviso enviado')
                        ->body("Se ha enviado el aviso a los miembros del grupo.")
                        ->success()
                        ->send();
                }),

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
