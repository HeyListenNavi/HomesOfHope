<?php

namespace App\Services\Whatsapp;

use App\Models\Applicant;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $templateLang;

    public function __construct(protected WhatsappClient $whatsappClient)
    {
        $this->templateLang = env('WHATSAPP_TEMPLATE_LANG', 'es');
    }

    public function send(Applicant $applicant, string $text, ?string $templateName = null, array $parameters = []): bool
    {
        if (!$applicant->conversation) {
            $applicant->conversation()->create();
            $applicant->load('conversation');
        }

        if ($this->hasActiveSession($applicant)) {
            $this->logMessage($applicant, $text);
            return $this->whatsappClient->sendText($applicant->chat_id, $text);
        }

        if ($templateName) {
            Log::info("Session expired for {$applicant->chat_id}. Using template: {$templateName}");
            $this->logMessage($applicant, '[TEMPLATE] ' . $templateName);
            return $this->whatsappClient->sendTemplate($applicant->chat_id, $templateName, $this->templateLang, $parameters);
        }

        Log::warning("Session expired for {$applicant->chat_id} and no fallback template was provided. Message not sent.");
        return false;
    }

    protected function hasActiveSession(Applicant $applicant): bool
    {
        if (!$applicant->conversation) return false;

        $lastUserMessage = $applicant->conversation->messages()
            ->where('role', 'user')
            ->latest()
            ->first();

        if (!$lastUserMessage) return false;

        return Carbon::parse($lastUserMessage->created_at)->diffInHours(now()) < 23;
    }

    protected function logMessage(Applicant $applicant, string $content): void
    {
        Message::create([
            'conversation_id' => $applicant->conversation->id,
            'phone' => $applicant->chat_id,
            'message' => $content,
            'role' => 'assistant',
            'name' => $applicant->applicant_name,
        ]);
    }
}
