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

class ViewApplicant extends ViewRecord
{
    protected static string $resource = ApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    
    public function form(Form $form): Form
    {
        // En este método, definimos la estructura del formulario.
        // No debemos iterar sobre los registros de la base de datos aquí.
        // El `getRecord()` ya te da acceso al Applicant que estás viendo.

        return $form
            ->schema([
                // Muestra los campos principales del Applicant
                Placeholder::make('chat_id')->label('Chat ID')->content($this->getRecord()->chat_id),
                Placeholder::make('curp')->label('CURP')->content($this->getRecord()->curp),
                
                // Muestra el `evaluation_data` si existe
                Placeholder::make('evaluation_data')
                    ->label('Datos de Evaluación')
                    ->visible(fn () => !empty($this->getRecord()->evaluation_data))
                    ->content(function (Applicant $record) {
                        $html = '<dl>';
                        foreach ($record->evaluation_data as $key => $value) {
                            $html .= '<dt><strong>' . ucwords(str_replace('_', ' ', $key)) . '</strong></dt>';
                            $html .= '<dd>' . (is_bool($value) ? ($value ? 'Sí' : 'No') : $value) . '</dd>';
                        }
                        $html .= '</dl>';
                        return new HtmlString($html);
                    }),
                
                // --- Sección para mostrar las respuestas ---
                // Usamos un Repeater para mostrar la relación `responses`
                Repeater::make('responses')
                    ->schema([
                        // Dentro del Repeater, mostramos los datos de cada respuesta.
                        Placeholder::make('pregunta')
                            ->label('Pregunta')
                            ->content(fn (ApplicantQuestionResponse $record) => new HtmlString("<strong>{$record->question->stage->name}</strong>: {$record->question_text_snapshot}")),
                        
                        Textarea::make('user_response')
                            ->label('Respuesta del usuario')
                            ->disabled()
                            ->rows(2),
                    ])
                    ->label('Respuestas del Solicitante')
                    ->relationship('responses') // Esto le dice a Filament que use la relación `responses`
                    ->dehydrated(false) // Deshabilita el guardado del Repeater
                    ->columnSpan('full')
                    ->columns(1)
                    ->defaultItems(0) // No se crean elementos por defecto
                    ->disableItemCreation() // No se pueden agregar elementos
                    ->disableItemDeletion() // No se pueden eliminar elementos
                    ->disableItemMovement(), // No se puede cambiar el orden
            ])
            ->columns(1);
    }
}