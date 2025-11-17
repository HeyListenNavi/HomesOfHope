<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class WhatsappApiNotificationService 
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $instance;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url');
        $this->apiKey = config('services.whatsapp.key');
    }

    public function sendGroupSelectionLink(Applicant $applicant): bool
    {
        $selectionUrl = URL::temporarySignedRoute(
            'group.selection.form',
            now()->addDays(3),
            ['applicant' => $applicant->id]
        );

        $message = "¡Felicidades, {$applicant->applicant_name}! Has sido aprobado(a) en el proceso. 🎉\n\n";
        $message .= "Para continuar, por favor elige la fecha y grupo para tu entrevista, haciendo clic en el siguiente enlace:\n\n";
        $message .= $selectionUrl . "\n\n";
        $message .= "Este enlace es personal y expirará en 3 días. ¡No lo compartas!";

        return $this->sendCustomMessage($applicant, $message);
    }

    public function sendCurrentQuestion(Applicant $applicant): bool
    {
        $currentQuestion = $applicant->currentQuestion;

        if (!$currentQuestion) {
            Log::warning("No hay una pregunta actual para el aplicante con chat_id {$applicant->chat_id}.");
            return false;
        }

        $message = $currentQuestion->question_text;

        return $this->sendCustomMessage($applicant, $message);
    }

    public function sendSuccessInfo(Applicant $applicant)
    {
        $message = "Felicidades! La cita para tu entrevista presencial fue " .
            "registrada con exito.\n" .
            "Por favor recuerda la siguiente informacion:\n" .
            "Tu cita es el dia: " . $applicant->group->date_time->toDateString() . "\n" .
            "A las: " . $applicant->group->date_time->toTimeString() . "\n" .
            "Con direccion: : " . $applicant->group->location . "\n" .
            "Ubicacion: " . $applicant->group->location_link . "\n";

        $message .= "No olvides leer la siguiente informacion importante: \n" . $applicant->group->message;

        $this->sendCustomMessage($applicant, $message);
    }


    public function sendCustomMessage(Applicant $applicant, string $message): bool
    {
        Message::create([
            'conversation_id' => $applicant->conversation->id,
            'phone' => $applicant->chat_id,
            'message' => $message,
            'role' => 'assistant',
            'name' => $applicant->applicant_name,
        ]);

        return $this->sendText($applicant->chat_id, $message);
    }

    protected function sendText(string $recipientId, string $message): bool
    {
        try {
            $url = "{$this->apiUrl}/messages";

            $response = Http::withHeaders([
                'D360-API-KEY' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'type' => 'text',
                'to' => $recipientId,
                'text' => [
                    'body' => $message,
                ],
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
