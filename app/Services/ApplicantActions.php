<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Stage;

class ApplicantActions{
    public static function resetApplicant( Applicant $applicant ){
        // Encuentra la primera etapa en el orden
        $firstStage = Stage::orderBy('order')->first();

        // Si no hay etapas, no hacemos nada.
        if (is_null($firstStage)) {
            return;
        }

        // Encuentra la primera pregunta de la primera etapa
        $firstQuestion = $firstStage->questions()->orderBy('order')->first();

        // Elimina las respuestas anteriores del aplicante para un inicio limpio
        $applicant->responses()->delete();

        // Actualiza el estado del aplicante
        $applicant->update([
            "current_stage_id" => $firstStage->id,
            "current_question_id" => $firstQuestion ? $firstQuestion->id : null,
            "process_status" => "in_progress",
        ]);
    }

    public static function approveStage( Applicant $applicant ){
        // Obtiene la etapa actual del aplicante.
        // Asumiendo una relación belongsTo llamada `currentStage`.
        $currentStage = $applicant->currentStage;

        // Si no hay una etapa actual, no hacemos nada
        if (is_null($currentStage)) {
            return;
        }

        // Busca la siguiente etapa por su orden
        $nextStage = Stage::where("order", ">", $currentStage->order)
                            ->orderBy('order')
                            ->first();

        // Si no hay una siguiente etapa, el proceso ha terminado,
        // podrías cambiar el estado a "finalizado" o similar
        if (is_null($nextStage)) {
            $applicant->update([
                "process_status" => "approved", 
            ]);
            return;
        }

        // Obtiene la primera pregunta de la siguiente etapa
        $firstQuestion = $nextStage->questions()->orderBy('order')->first();

        // Actualiza el estado del aplicante
        $applicant->update([
            "current_stage_id" => $nextStage->id,
            "current_question_id" => $firstQuestion ? $firstQuestion->id : null,
            "process_status" => "in_progress",
        ]);
    }
}
