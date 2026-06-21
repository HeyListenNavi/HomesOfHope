<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use App\Services\WhatsappApiNotificationService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewApplicant extends ViewRecord
{
    protected static string $resource = ApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('goToWhatsapp')
                ->label('WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('primary')
                ->url(function () {
                    $number = $this->record->chat_id;
                    $text = 'Hola! Soy del equipo de Casas de Esperanza, y me gustaría realizarte algunas preguntas sobre tu aplicación';
                    $encodedMessage = urlencode($text);

                    return "https://wa.me/{$number}?text={$encodedMessage}";
                })
                ->openUrlInNewTab(),

            Actions\Action::make('sendTemplate')
                ->label('Enviar template')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Enviar mensaje')
                ->modalDescription('¿Seguro que deseas enviar el template de WhatsApp?')
                ->action(function (Applicant $applicant) {
                    $WhatsApp = new WhatsappApiNotificationService;
                    $WhatsApp->sendTemplate($applicant);
                }),

            Actions\EditAction::make(),
        ];
    }
}
