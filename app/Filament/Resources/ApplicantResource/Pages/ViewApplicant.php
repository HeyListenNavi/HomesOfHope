<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use App\Models\Question;
use Filament\Actions;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ViewApplicant extends ViewRecord
{
    protected static string $resource = ApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    
    // Elimina este método mount() completamente
    // public function mount(int | string $record): void
    // {
    //     parent::mount($record);
    //     $this->form->fill($this->getFormFields());
    // }

    public function form(Form $form): Form
    {
        $applicant = $this->getRecord();
        $formFields = [];
        $questions = $applicant->responses()->with('question.stage')->orderBy('created_at')->get();

        foreach ($questions as $response) {
            $question = $response->question;
            $stageName = $question->stage->name ?? 'Sin etapa';
            $key = $question->key;
            $questionText = $response->question_text_snapshot;
            $userResponse = $response->user_response;
            
            $formFields[] = Textarea::make($key)
                ->label(new HtmlString("<strong>{$stageName}</strong>: {$questionText}"))
                ->default($userResponse)
                ->disabled() // Agregamos disabled() para una vista de solo lectura
                ->extraAttributes(['data-question-id' => $response->question_id]);
        }

        return $form
            ->schema($formFields)
            ->columns(1);
    }
    
    public function save(): void
    {
        // Tu lógica de guardado aquí
    }
}