<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Filament\Resources\ApplicantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Applicant;
use App\Services\WhatsappApiNotificationService;

class EditApplicant extends EditRecord
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

                    $text = "Hola! Soy del equipo de Casas de Esperanza, y me gustaría realizarte algunas preguntas sobre tu aplicación";

                    $encodedMessage = urlencode($text);

                    return "https://wa.me/{$number}?text={$encodedMessage}";
                })
                ->openUrlInNewTab(),

            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
