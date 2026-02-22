<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use Filament\Actions;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use App\Models\ApplicantQuestionResponse;
use App\Services\WhatsappApiNotificationService;


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
                    $text = "Hola! Soy del equipo de Casas de Esperanza, y me gustaría realizarte algunas preguntas sobre tu aplicación";
                    $encodedMessage = urlencode($text);
                    return "https://wa.me/{$number}?text={$encodedMessage}";
                })
                ->openUrlInNewTab(),

            Actions\EditAction::make(),
        ];
    }
}
