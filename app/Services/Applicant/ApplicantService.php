<?php

namespace App\Services\Applicant;

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

    public function reSendGroupSelectionLink(Applicant $applicant): void
    {
        $applicant->update([
            'process_status' => 'staff_approved',
            'group_id' => null,
            'confirmation_status' => 'pending',
        ]);

        $this->sendSelectionLink($applicant);
    }

    public function approveApplicantFinal(Applicant $applicant): void
    {
        $applicant->update([
            'process_status' => 'staff_approved',
            'group_id' => null,
        ]);

        $this->sendSelectionLink($applicant);
    }

    public function rejectApplicant(Applicant $applicant, string $reason): void
    {
        $rejectionMessages = [
            'no_children' => 'Nuestro programa está enfocado en apoyar a familias que tengan hijos menores de edad viviendo con ellos. En el caso de personas adultas mayores, es posible aplicar únicamente si tienen menores bajo su tutela legal y pueden presentar la documentación que lo compruebe. Además, los menores deben estar actualmente inscritos en primaria o secundaria.',
            'contract_issues' => 'Para nosotros es indispensable que usted o su pareja sea el propietario del terreno, cuente con documentación legal que lo acredite, esperamos que usted pueda encontrar la ayuda que usted necesita.',
            'not_owner' => 'Para nosotros es indispensable que usted o su pareja sea el propietario del terreno, cuente con documentación legal que lo acredite, esperamos que usted pueda encontrar la ayuda que usted necesita.',
            'lives_too_far' => 'Solo estamos considerando a las familias que viven en su terreno o en la misma colonia. Esperamos que encuentre la ayuda que usted necesita. Si esta situación cambia en el futuro, usted podrá volver a aplicar después de tener más de 8 meses viviendo cerca de su terreno o en el mismo.',
            'less_than_a_year' => 'Necesitas tener una antigüedad mínima de un año con tu terreno o vivir en tu terreno por al menos 8 meses para poder aplicar y quedando sujeto a revisiones o espera dependiendo del cumplimiento de tus pagos mensuales por tu terreno.',
            'late_payments' => 'Vimos que tienes pagos atrasados con tu terreno. Por ahora no podemos seguir con tu proceso, ya que el programa pide que estés al corriente con tus pagos para participar.',
            'out_of_coverage' => 'Lamentablemente, no estamos construyendo en la colonia donde se encuentra su terreno 😔. Nos encantaría poder ayudar a todos, pero nuestros recursos son limitados. ¡No somos la única organización construyendo hogares 🏠! Le animamos a que continúe investigando para ver si hay otras organizaciones trabajando en su colonia. ¡Gracias por su comprensión y esperamos que encuentre la ayuda que necesita!',
            'other_family_members' => 'Sabemos que muchas familias hacen un gran esfuerzo para cuidar de sus hijos o nietos, y entendemos la necesidad que existe. Lamentablemente, en este momento no nos es posible apoyar a todos los casos. Este apoyo está dirigido únicamente a abuelos, tíos o hermanos que son los responsables permanentes del cuidado de los menores, ya sea porque cuentan con su custodia legal o porque los padres ya no están a cargo de ellos. Si usted cuida a los niños mientras sus padres trabajan, quienes pueden realizar el registro son los padres, siempre y cuando el terreno donde se construiría la casa esté a nombre de ellos. Agradecemos mucho su comprensión y le deseamos muchas bendiciones.',
            'out_of_coverage_approved' => 'Revisamos cuidadosamente la ubicación de tu terreno y, lamentablemente, está fuera de las zonas donde podemos construir. Por este motivo, *tu proceso no podrá continuar*. Te pedimos que *ya no te presentes a la entrevista*, ya que no será posible seguir con la solicitud. Sabemos que esta noticia puede ser decepcionante y nos gustaría poder apoyar a todas las familias que lo necesitan. Oramos para que pronto encuentren la ayuda que buscan. Dios les bendiga.',
        ];

        $reasonMessage = $rejectionMessages[$reason] ?? $reason;

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
    }
}
