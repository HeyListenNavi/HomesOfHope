<?php

namespace App\Services;

use App\Models\Applicant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Servicio simple para enviar notificaciones a través de Evolution API.
 */
class EvolutionApiNotificationService
{
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        // Asegúrate de tener estas variables en tu archivo .env
        // y en config/services.php
        $this->apiUrl = config('services.evolution.url');
        $this->apiKey = config('services.evolution.key');
    }

    /**
     * Envía un mensaje al aplicante con un enlace para que elija su grupo.
     *
     * @param Applicant $applicant
     * @return bool
     */
    public function sendGroupSelectionLink(Applicant $applicant): bool
    {
        $selectionUrl = URL::temporarySignedRoute(
            'group.selection.form', // Nombre de la ruta que crearemos más adelante
            now()->addDays(3),       // El enlace será válido por 3 días
            ['applicant' => $applicant->id] // Usamos el ID para seguridad
        );

        $message = "¡Felicidades, {$applicant->applicant_name}! Has sido aprobado(a) en el proceso. 🎉\n\n";
        $message .= "Para continuar, por favor elige la fecha y grupo para tu entrevista, haciendo clic en el siguiente enlace:\n\n";
        $message .= $selectionUrl . "\n\n";
        $message .= "Este enlace es personal y expirará en 3 días. ¡No lo compartas!";

        return $this->sendText($applicant->chat_id, $message);
    }

    /**
     * Envía la pregunta actual al aplicante.
     *
     * @param Applicant $applicant
     * @return bool
     */
    public function sendCurrentQuestion(Applicant $applicant): bool
    {
        $currentQuestion = $applicant->currentQuestion;

        if (!$currentQuestion) {
            Log::warning("No hay una pregunta actual para el aplicante con chat_id {$applicant->chat_id}.");
            return false;
        }

        $message = $currentQuestion->question_text;

        return $this->sendText($applicant->chat_id, $message);
    }

    /**
     * Envía un mensaje de texto personalizado a un aplicante.
     *
     * @param Applicant $applicant
     * @param string $message
     * @return bool
     */
    public function sendCustomMessage(Applicant $applicant, string $message): bool
    {
        return $this->sendText($applicant->chat_id, $message);
    }

    /**
     * Método auxiliar privado para manejar la lógica de la llamada a la API.
     *
     * @param string $recipientId
     * @param string $message
     * @return bool
     */
    protected function sendText(string $recipientId, string $message): bool
    {
        try {
            $response = Http::post("{$this->apiUrl}/message/sendText/{$recipientId}", [
                'number' => $recipientId,
                'options' => ['delay' => 1200],
                'textMessage' => ['text' => $message],
                'token' => $this->apiKey
            ]);

            if ($response->successful()) {
                Log::info("Mensaje de texto enviado a {$recipientId}.");
                return true;
            }

            Log::error("Error al enviar mensaje a {$recipientId}: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::critical("Excepción al enviar mensaje con Evolution API: " . $e->getMessage());
            return false;
        }
    }
}
