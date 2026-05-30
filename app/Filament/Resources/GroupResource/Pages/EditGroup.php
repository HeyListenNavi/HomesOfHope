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
            
            Actions\Action::make('takeAttendance')
                ->label('Pasar Lista')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->url(fn(Group $record) => route('attendance.page', $record)),
            
            Actions\ActionGroup::make([
                Actions\Action::make('resendInfo')
                    ->label('Reenviar Información')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Reenviar información')
                    ->modalDescription('¿Estás seguro de que deseas reenviar la información de la entrevista a todos los miembros de este grupo?')
                    ->action(function (Group $record, GroupService $groupService) {
                        $groupService->reSendGroupMessage($record);

                        Notification::make()->title('Información enviada')->body('Se ha enviado la información a los miembros del grupo.')->success()->send();
                    }),

                Actions\Action::make('sendAnnouncement')
                    ->label('Enviar Aviso')
                    ->icon('heroicon-m-megaphone')
                    ->color('warning')
                    ->form([Forms\Components\Textarea::make('announcement')->label('Aviso')->placeholder('Escribe aquí el aviso para los miembros...')->required()->rows(5)])
                    ->action(function (Group $record, array $data, GroupService $groupService) {
                        $groupService->sendCustomMessageToGroup($record, $data['announcement']);

                        Notification::make()->title('Aviso enviado')->body('Se ha enviado el aviso a los miembros del grupo.')->success()->send();
                    }),
            ])
                ->label('Contactar')
                ->button()
                ->icon('heroicon-m-phone')
                ->color('primary'),

            Actions\ActionGroup::make([
                Actions\Action::make('exportPdf')
                    ->label('PDF')
                    ->icon('heroicon-m-document-text')
                    ->color('danger')
                    ->action(function ($record) {
                        $record->load('applicants');
                        $pdf = Pdf::loadView('exports.group-export', ['group' => $record])
                            ->setOption(['defaultFont' => 'sans-serif'])
                            ->setOption(['isHtml5ParserEnabled' => true]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, $record->name . '.pdf');
                    }),

                Actions\Action::make('exportExcel')
                    ->label('Excel')
                    ->icon('heroicon-m-table-cells')
                    ->color('success')
                    ->action(function ($record) {
                        return Excel::download(new GroupApplicantsExport($record), "Grupo - {$record->name}.xlsx");
                    }),
            ])
                ->label('Exportar')
                ->button()
                ->icon('heroicon-m-arrow-up-tray')
                ->color('info'),
        ];
    }
}
