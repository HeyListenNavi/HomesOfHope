<?php

namespace App\Services\Applicant;

use App\Enums\ApplicantStatus;
use App\Enums\RejectionReason;
use App\Models\Applicant;
use App\Models\ApplicantQuestionResponse;
use App\Models\Question;
use App\Models\Stage;
use App\Services\Whatsapp\WhatsappService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class ApplicantService
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function startApplicantQuestions(Applicant $applicant): void
    {
        Log::info("Starting questions flow for applicant ID {$applicant->id}.");

        $firstStage = Stage::orderBy('order')->first();

        if (is_null($firstStage)) {
            Log::warning("No stages found when trying to start questions for applicant ID {$applicant->id}.");

            return;
        }

        $firstQuestion = $firstStage->questions()->orderBy('order')->first();

        if (is_null($firstQuestion)) {
            Log::warning("No questions found for first stage when trying to start questions for applicant ID {$applicant->id}.");

            return;
        }

        $allQuestions = Question::orderBy('stage_id')->orderBy('order')->get();
        foreach ($allQuestions as $question) {
            ApplicantQuestionResponse::create([
                'applicant_id' => $applicant->id,
                'question_id' => $question->id,
                'question_text_snapshot' => $question->question_text,
                'user_response' => null,
            ]);
        }

        $applicant->update([
            'current_stage_id' => $firstStage->id,
            'current_question_id' => $firstQuestion->id,
            'process_status' => 'in_progress',
            'current_step' => 'ask_question',
        ]);

        $startingMessage = "Hola {$applicant->applicant_name}! Te saluda el equipo de Casas de Esperanza.\n".
            "Tu proceso de aplicación ha comenzado. Por favor, responde la siguiente pregunta para continuar:\n\n".

            "{$firstQuestion->question_text}\n\n".

            'Estamos aquí para acompañarte. Cuenta con nosotros 🏠';

        $this->whatsappService->send($applicant, $startingMessage, 'inicio_aplicacion', [
            'nombre' => $applicant->applicant_name,
            'pregunta' => $firstQuestion->question_text,
        ]);
    }

    public function resetApplicant(Applicant $applicant): void
    {
        Log::info("Resetting process for applicant ID {$applicant->id}.");

        $firstStage = Stage::orderBy('order')->first();

        if (is_null($firstStage)) {
            $message = 'Lo sentimos, no se puede reiniciar el proceso. No se encontraron etapas.';
            $this->whatsappService->send($applicant, $message, 'error_reinicio_etapa');

            return;
        }

        $firstQuestion = $firstStage->questions()->orderBy('order')->first();

        try {
            if ($applicant->conversation) {
                $applicant->conversation->messages()->delete();
            }
        } catch (Exception $e) {
            Log::error("Error deleting conversation messages for applicant ID {$applicant->id}: ".$e->getMessage());
        }

        $applicant->responses()->delete();

        $applicant->update([
            'current_stage_id' => $firstStage->id,
            'current_question_id' => $firstQuestion ? $firstQuestion->id : null,
            'process_status' => 'in_progress',
            'group_id' => null,
        ]);

        $resetMessage = 'Hola te saluda el equipo de Casas de Esperanza. Hemos reiniciado tu proceso para que puedas comenzar nuevamente desde el inicio. En breve recibirás las indicaciones de nuestro asistente virtual. Por favor sigue los pasos y responde con calma. Estamos para apoyarte.';
        $this->whatsappService->send($applicant, $resetMessage, 'reiniciar_aplicante');

        if ($firstQuestion) {
            $this->sendQuestion($applicant, $firstQuestion);
        }
    }

    public function approveStage(Applicant $applicant): void
    {
        Log::info("Approving current stage for applicant ID {$applicant->id}.");

        $currentStage = $applicant->currentStage;

        if (is_null($currentStage)) {
            Log::warning("No current stage found for applicant ID {$applicant->id}.");

            return;
        }

        $nextStage = Stage::where('order', '>', $currentStage->order)
            ->orderBy('order')
            ->first();

        if (is_null($nextStage)) {
            $applicant->update(['process_status' => 'staff_approved']);
            $this->sendSelectionLink($applicant);

            return;
        }

        $firstQuestion = $nextStage->questions()->orderBy('order')->first();

        $applicant->update([
            'current_stage_id' => $nextStage->id,
            'current_question_id' => $firstQuestion ? $firstQuestion->id : null,
            'process_status' => 'in_progress',
        ]);

        if ($firstQuestion) {
            $confirmationMessage = 'Hola te saluda el equipo de Casas de Esperanza, hemos revisado detenidamente sus respuestas y puede continuar con el proceso, le pedimos paciencia a nuestro asistente virtual.';
            $this->whatsappService->send($applicant, $confirmationMessage, 'etapa_aprobada');
            $this->sendQuestion($applicant, $firstQuestion);
        }
    }

    public function reSendCurrentQuestion(Applicant $applicant): void
    {
        if (! $applicant->currentQuestion) {
            Log::warning("No current question for applicant chat_id {$applicant->chat_id}.");

            return;
        }

        if ($applicant->process_status !== 'in_process') {
            $applicant->update(['process_status' => 'in_progress']);
        }

        $this->sendQuestion($applicant, $applicant->currentQuestion);
    }

    public function sendQuestion(Applicant $applicant, Question $question): void
    {
        $this->whatsappService->send($applicant, $question->question_text, 'enviar_pregunta', [
            'pregunta' => $question->question_text,
        ]);
    }

    public function sendSelectionLink(Applicant $applicant): void
    {
        $selectionUrl = URL::temporarySignedRoute(
            'group.selection.form',
            now()->addDays(3),
            ['applicant' => $applicant->id]
        );

        $message = "¡Felicidades, {$applicant->applicant_name}! Has sido aprobado(a) en el proceso. 🎉\n\n";
        $message .= "Para continuar, por favor elige la fecha y grupo para tu entrevista, haciendo clic en el siguiente enlace:\n\n";
        $message .= $selectionUrl."\n\n";
        $message .= 'Este enlace es personal y expirará en 3 días. ¡No lo compartas!';

        $this->whatsappService->send($applicant, $message, 'enviar_link_de_entrevista', [
            'link_de_entrevista' => $selectionUrl,
            'nombre' => $applicant->applicant_name,
        ]);
    }

    public function reSendGroupSelectionLink(Applicant $applicant): bool
    {
        if (! in_array($applicant->process_status, [ApplicantStatus::Approved, ApplicantStatus::StaffApproved], true)) {
            return false;
        }

        $applicant->update([
            'group_id' => null,
            'confirmation_status' => 'pending',
        ]);

        $this->sendSelectionLink($applicant);

        return true;
    }

    public function approveApplicantFinal(Applicant $applicant): void
    {
        $applicant->update([
            'process_status' => 'staff_approved',
            'group_id' => null,
        ]);

        $this->sendSelectionLink($applicant);
    }

    public function setProcessStatusSilently(Applicant $applicant, string $status): void
    {
        $applicant->update([
            'process_status' => $status,
            'group_id' => null,
        ]);
    }

    public function rejectApplicant(Applicant $applicant, string $reason): void
    {
        $reasonMessage = RejectionReason::tryFrom($reason)?->message() ?? $reason;

        $applicant->update([
            'process_status' => 'staff_rejected',
            'rejection_reason' => $reason,
            'group_id' => null,
        ]);

        $message = "Hola te saluda el equipo de Casas de Esperanza, agradecemos profundamente que hayas pensado en nosotros para buscar apoyo. Revisamos tu solicitud y, aunque quisiéramos ayudar a todos, en este momento no podemos avanzar con tu proceso para una Casa de Esperanza.\n".$reasonMessage."\nDeseamos de corazón que encuentres pronto la ayuda que necesitas y oramos por bendición y fortaleza para ti y tu familia.";

        $this->whatsappService->send($applicant, $message, 'rechazar_aplicante', ['razon' => $reasonMessage]);
    }

    public function sendCustomMessage(Applicant $applicant, string $message): void
    {
        $this->whatsappService->send($applicant, $message);

        $applicant->save();
    }

    public function sendCompleteProfileNotification(Applicant $applicant, string $profileUrl): void
    {
        $message = "Hola {$applicant->applicant_name}! Hemos terminado de escanear los documentos de tu familia para tu solicitud.\n\n";
        $message .= "Por favor ingresa al enlace para revisar y confirmar que los datos sean correctos:\n\n";
        $message .= $profileUrl;

        $this->whatsappService->send($applicant, $message, 'verificacion_de_documentos', [
            'nombre' => $applicant->applicant_name,
            'link' => $profileUrl,
        ]);
    }
}
