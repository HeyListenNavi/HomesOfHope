<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Response;


class WhatsappApiNotificationService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $instance;
    protected string $templateName;
    protected string $templateLang;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url');
        $this->apiKey = config('services.whatsapp.key');
        $this->templateName = env('WHATSAPP_TEMPLATE_APPROVED');
        $this->templateLang = env('WHATSAPP_TEMPLATE_LANG', 'es');
    }

    public function sendGroupSelectionLink(Applicant $applicant)
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

        $this->sendCustomMessage($applicant, $message, 'enviar_link_de_entrevista', ['link_de_entrevista' => $selectionUrl, 'nombre' => $applicant->applicant_name]);
    }

    public function sendCurrentQuestion(Applicant $applicant)
    {
        $currentQuestion = $applicant->currentQuestion;

        if (!$currentQuestion) {
            Log::warning("No hay una pregunta actual para el aplicante con chat_id {$applicant->chat_id}.");
            return false;
        }

        $message = $currentQuestion->question_text;

        $this->sendCustomMessage($applicant, $message, 'enviar_pregunta', ['pregunta' => $message]);
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

        $this->sendCustomMessage($applicant, $message, 'enviar_informacion_de_entrevista', [
            'dia' => $applicant->group->date_time->toDateString(),
            'hora' => $applicant->group->date_time->toTimeString(),
            'direccion' => $applicant->group->location,
            'ubicacion' => $applicant->group->location_link,
            'detalles_extra' => $applicant->group->message,
        ]);
    }


    public function sendCustomMessage(Applicant $applicant, string $message, ?string $templateName = null, array $parameters = [])
    {
        Message::create([
            'conversation_id' => $applicant->conversation->id,
            'phone' => $applicant->chat_id,
            'message' => $message,
            'role' => 'assistant',
            'name' => $applicant->applicant_name,
        ]);

        if ($this->hasActiveSession($applicant)) {
            $this->sendText($applicant->chat_id, $message);
            return;
        }

        if ($templateName) {
            Log::info("Sesión expirada para {$applicant->chat_id}. Usando template: {$templateName}");
            $this->sendTemplate($applicant, $templateName, $parameters);
        } else {
            Log::warning("Sesión expirada para {$applicant->chat_id} y no se proporcionó un template de respaldo. El mensaje no se envió a Meta.");
        }
    }

    private function hasActiveSession(Applicant $applicant): bool
    {
        $lastUserMessage = $applicant->conversation->messages()
            ->where('role', 'user')
            ->latest()
            ->first();

        if (!$lastUserMessage) return false;

        return Carbon::parse($lastUserMessage->created_at)->diffInHours(now()) < 23;
    }

    protected function sendText(string $recipientId, string $message): bool
    {
        try {
            $url = "{$this->apiUrl}/messages";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
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

    public function sendTemplate(Applicant $applicant, ?string $templateName = null, array $parameters = [])
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $applicant->chat_id,
            'type' => 'template',
            'template' => [
                'name' => $templateName ?? $this->templateName,
                'language' => [
                    'code' => $this->templateLang,
                ],
            ],
        ];

        if (!empty($parameters)) {
            $payload['template']['components'][] = [
                'type' => 'body',
                'parameters' => collect($parameters)->map(fn($value) => [
                    'type' => 'text',
                    'text' => $value,
                ])->toArray(),
            ];
        }

        $url = "{$this->apiUrl}/messages";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if ($response->failed()) {
            Log::error("Error al enviar template a {$applicant->chat_id}: " . $response->body());
            return;
        }

        Log::info("Template enviado correctamente a {$applicant->chat_id}.");

        Message::create([
            'conversation_id' => $applicant->conversation->id,
            'phone' => $applicant->chat_id,
            'message' => '[TEMPLATE] ' . ($templateName ?? $this->templateName),
            'role' => 'assistant',
            'name' => $applicant->applicant_name,
        ]);
    }
}
