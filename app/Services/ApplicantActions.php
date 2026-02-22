<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Stage;
use App\Services\WhatsappApiNotificationService;
use Exception;
use Illuminate\Support\Facades\Log;

class ApplicantActions
{
    public static function resetApplicant(Applicant $applicant): void
    {
        Log::info("Reiniciando el proceso para el aplicante con ID {$applicant->id}.");
        $notificationService = new WhatsappApiNotificationService();

        $firstStage = Stage::orderBy('order')->first();

        if (is_null($firstStage)) {
            $message = "Lo sentimos, no se puede reiniciar el proceso. No se encontraron etapas.";
            $notificationService->sendCustomMessage($applicant, $message, 'error_reinicio_etapa');
            Log::warning("No se encontraron etapas para reiniciar el proceso del aplicante con ID {$applicant->id}.");
            return;
        }

        $firstQuestion = $firstStage->questions()->orderBy('order')->first();

        try {
            if ($applicant->conversation) {
                $applicant->conversation->messages()->delete();
                Log::info("Mensajes de la conversación con chat_id {$applicant->chat_id} eliminados.");
            }
        } catch (Exception $e) {
            Log::error("Error al intentar eliminar los mensajes de la conversación para el aplicante con ID {$applicant->id}: " . $e->getMessage());
        }

        $applicant->responses()->delete();

        $applicant->update([
            "current_stage_id" => $firstStage->id,
            "current_question_id" => $firstQuestion ? $firstQuestion->id : null,
            "process_status" => "in_progress",
            "group_id" => null,
        ]);
        Log::info("Grupo del aplicante con ID {$applicant->id} establecido en null.");

        $resetMessage = "Hola te saluda el equipo de Casas de Esperanza. Hemos reiniciado tu proceso para que puedas comenzar nuevamente desde el inicio. En breve recibirás las indicaciones de nuestro asistente virtual. Por favor sigue los pasos y responde con calma. Estamos para apoyarte.";
        $notificationService->sendCustomMessage($applicant, $resetMessage, 'reiniciar_aplicante');

        if ($firstQuestion) {
            $notificationService->sendCurrentQuestion($applicant);
        }
    }

    public static function approveStage(Applicant $applicant): void
    {
        Log::info("Aprobando la etapa actual para el aplicante con ID {$applicant->id}.");
        $notificationService = new WhatsappApiNotificationService();

        $currentStage = $applicant->currentStage;

        if (is_null($currentStage)) {
            Log::warning("No se encontró una etapa actual para el aplicante con ID {$applicant->id}.");
            return;
        }

        $nextStage = Stage::where("order", ">", $currentStage->order)
            ->orderBy('order')
            ->first();

        if (is_null($nextStage)) {
            Log::info("El proceso del aplicante con ID {$applicant->id} ha finalizado. Enviando enlace de selección de grupo.");
            $applicant->update([
                "process_status" => "staff_approved",
            ]);
            $notificationService->sendGroupSelectionLink($applicant);
            return;
        }

        $firstQuestion = $nextStage->questions()->orderBy('order')->first();

        $applicant->update([
            "current_stage_id" => $nextStage->id,
            "current_question_id" => $firstQuestion ? $firstQuestion->id : null,
            "process_status" => "in_progress",
        ]);
        Log::info("Aplicante con ID {$applicant->id} movido a la siguiente etapa: {$nextStage->name}.");

        if ($firstQuestion) {
            $confirmationMessage = "Hola te saluda el equipo de Casas de Esperanza, hemos revisado detenidamente sus respuestas y puede continuar con el proceso, le pedimos paciencia a nuestro asistente virtual.";
            $notificationService->sendCustomMessage($applicant, $confirmationMessage, 'etapa_aprobada');
            $notificationService->sendCustomMessage($applicant, $firstQuestion->question_text, 'enviar_pregunta', ['pregunta' => $firstQuestion->question_text]);
        }
    }

    public static function reSendCurrentQuestion(Applicant $applicant): void
    {
        Log::info("Reenviando la pregunta actual al aplicante con ID {$applicant->id}.");
        $notificationService = new WhatsappApiNotificationService();

        $currentQuestion = $applicant->currentQuestion;

        if (!$currentQuestion) {
            Log::warning("No hay una pregunta actual para el aplicante con chat_id {$applicant->chat_id}.");
            return;
        }

        $message = $currentQuestion->question_text;

        $notificationService->sendCustomMessage($applicant, $message, 'reenviar_pregunta', ['pregunta' => $message]);
    }

    public static function reSendGroupSelectionLink(Applicant $applicant): void
    {
        Log::info("Reenviando enlace de selección de grupo al aplicante con ID {$applicant->id}.");
        $notificationService = new WhatsappApiNotificationService();

        $applicant->update([
            "process_status" => "staff_approved",
            "group_id" => null,
        ]);
        Log::info("Grupo del aplicante con ID {$applicant->id} establecido en null antes de reenviar el enlace.");

        $notificationService->sendGroupSelectionLink($applicant);
    }

    public static function approveApplicantFinal(Applicant $applicant): void
    {
        Log::info("Aprobando al aplicante con ID {$applicant->id} de forma definitiva.");
        $notificationService = new WhatsappApiNotificationService();

        $applicant->update([
            "process_status" => "staff_approved",
            "group_id" => null,
        ]);
        Log::info("Grupo del aplicante con ID {$applicant->id} establecido en null después de la aprobación final.");

        $notificationService->sendGroupSelectionLink($applicant);
    }

    public static function rejectApplicant(Applicant $applicant, string $reason): void
    {
        Log::info("Rechazando al aplicante con ID {$applicant->id}.");

        $applicant->update([
            "process_status" => "staff_rejected",
            "rejection_reason" => $reason,
        ]);

        $notificationService = new WhatsappApiNotificationService();

        $message = "Hola te saluda el equipo de Casas de Esperanza, agradecemos profundamente que hayas pensado en nosotros para buscar apoyo. Revisamos tu solicitud y, aunque quisiéramos ayudar a todos, en este momento no podemos avanzar con tu proceso para una Casa de Esperanza. Deseamos de corazón que encuentres pronto la ayuda que necesitas y oramos por bendición y fortaleza para ti y tu familia.";

        $notificationService->sendCustomMessage($applicant, $message, 'rechazo_solicitud');
    }

    public static function sendCustomMessage(Applicant $applicant, string $message): void
    {
        Log::info("Enviando mensaje personalizado al aplicante con ID {$applicant->id}.");
        $notificationService = new WhatsappApiNotificationService();
        $notificationService->sendCustomMessage($applicant, $message);
    }
}
