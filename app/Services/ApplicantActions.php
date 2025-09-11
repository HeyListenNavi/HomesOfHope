<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Stage;
use App\Services\EvolutionApiNotificationService;
use Exception;
use Illuminate\Support\Facades\Log;

class ApplicantActions{
    public static function resetApplicant(Applicant $applicant): void
    {
        Log::info("Reiniciando el proceso para el aplicante con ID {$applicant->id}.");
        $notificationService = new EvolutionApiNotificationService();

        // Encuentra la primera etapa en el orden
        $firstStage = Stage::orderBy('order')->first();

        // Si no hay etapas, no hacemos nada.
        if (is_null($firstStage)) {
            $message = "Lo sentimos, no se puede reiniciar el proceso. No se encontraron etapas.";
            $notificationService->sendCustomMessage($applicant, $message);
            Log::warning("No se encontraron etapas para reiniciar el proceso del aplicante con ID {$applicant->id}.");
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

        // Envía el mensaje de reinicio al usuario
        $resetMessage = "Tu solicitud ha sido reiniciada. ¡Comencemos de nuevo!";
        $notificationService->sendCustomMessage($applicant, $resetMessage);

        // Envía la primera pregunta del primer stage si existe
        if ($firstQuestion) {
            $notificationService->sendCurrentQuestion($applicant);
        }
    }

    public static function approveStage(Applicant $applicant): void
    {
        Log::info("Aprobando la etapa actual para el aplicante con ID {$applicant->id}.");
        $notificationService = new EvolutionApiNotificationService();
        
        // Obtiene la etapa actual del aplicante.
        // Asumiendo una relación belongsTo llamada `currentStage`.
        $currentStage = $applicant->currentStage;

        // Si no hay una etapa actual, no hacemos nada
        if (is_null($currentStage)) {
            Log::warning("No se encontró una etapa actual para el aplicante con ID {$applicant->id}.");
            return;
        }

        // Busca la siguiente etapa por su orden
        $nextStage = Stage::where("order", ">", $currentStage->order)
            ->orderBy('order')
            ->first();

        // Si no hay una siguiente etapa, el proceso ha terminado
        if (is_null($nextStage)) {
            Log::info("El proceso del aplicante con ID {$applicant->id} ha finalizado. Enviando enlace de selección de grupo.");
            $applicant->update([
                "process_status" => "approved", 
            ]);
            // El proceso ha terminado, enviamos el enlace de selección de grupo.
            $notificationService->sendGroupSelectionLink($applicant);
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
        Log::info("Aplicante con ID {$applicant->id} movido a la siguiente etapa: {$nextStage->name}.");
        
        // Envía la primera pregunta del siguiente stage
        if ($firstQuestion) {
            $notificationService->sendCustomMessage($applicant, $firstQuestion->question_text);
        }
    }

    /**
     * Reenvía la pregunta actual al aplicante.
     *
     * @param Applicant $applicant
     * @return void
     */
    public static function reSendCurrentQuestion(Applicant $applicant): void
    {
        Log::info("Reenviando la pregunta actual al aplicante con ID {$applicant->id}.");
        $notificationService = new EvolutionApiNotificationService();
        $notificationService->sendCurrentQuestion($applicant);
    }

    /**
     * Reenvía el enlace de selección de grupo al aplicante.
     *
     * @param Applicant $applicant
     * @return void
     */
    public static function reSendGroupSelectionLink(Applicant $applicant): void
    {
        Log::info("Reenviando enlace de selección de grupo al aplicante con ID {$applicant->id}.");
        $notificationService = new EvolutionApiNotificationService();
        $notificationService->sendGroupSelectionLink($applicant);
    }

    /**
     * Aprueba al aplicante de manera definitiva y le envía el enlace para la selección de grupo.
     * Este método se utiliza cuando se desea aprobar al aplicante manualmente,
     * sin importar la etapa o pregunta en la que se encuentre.
     *
     * @param Applicant $applicant
     * @return void
     */
    public static function approveApplicantFinal(Applicant $applicant): void
    {
        Log::info("Aprobando al aplicante con ID {$applicant->id} de forma definitiva.");
        $notificationService = new EvolutionApiNotificationService();
        
        // Actualiza el estado del aplicante a "approved"
        $applicant->update([
            "process_status" => "approved",
            "is_approved" => true, // Asumiendo que existe esta columna
        ]);

        // Envía el mensaje de aprobación final con el enlace de selección de grupo
        $notificationService->sendGroupSelectionLink($applicant);
    }
    
    /**
     * Envía un mensaje personalizado al aplicante.
     *
     * @param Applicant $applicant
     * @param string $message
     * @return void
     */
    public static function sendCustomMessage(Applicant $applicant, string $message): void
    {
        Log::info("Enviando mensaje personalizado al aplicante con ID {$applicant->id}.");
        $notificationService = new EvolutionApiNotificationService();
        $notificationService->sendCustomMessage($applicant, $message);
    }
}
