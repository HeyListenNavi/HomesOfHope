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

    public function mount(int | string $record): void
    {
        parent::mount($record);
        $this->form->fill($this->getFormFields());
    }

    protected function getFormSchema(): array
    {
        $applicant = $this->record;

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
                ->extraAttributes(['data-question-id' => $response->question_id]);
        }

        return [
            ...$formFields,
        ];
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        $applicant = $this->record;
        
        $evaluationData = $applicant->evaluation_data ?? [];

        foreach ($data as $key => $value) {
            // Buscamos la respuesta en el historial y la actualizamos
            $question = Question::where('key', $key)->first();
            if ($question) {
                $response = $applicant->responses()->where('question_id', $question->id)->first();
                if ($response) {
                    $response->update([
                        'user_response' => $value,
                    ]);
                }
            }

            // También actualizamos el JSON de evaluación general
            $evaluationData[$key] = $value;
        }

        $applicant->update(['evaluation_data' => $evaluationData]);
        
        $this->getRedirectUrl();
    }
}