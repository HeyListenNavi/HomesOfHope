<?php

namespace App\Services\Whatsapp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappClient
{
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url');
        $this->apiKey = config('services.whatsapp.key');
    }

    public function sendText(string $to, string $text): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/messages", [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'type' => 'text',
                'to' => $to,
                'text' => [
                    'body' => $text,
                ],
            ]);

            if ($response->successful()) {
                Log::info("Text message sent to {$to}.");
                return true;
            }

            Log::error("Error sending message to {$to}: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::critical("Exception sending message to {$to}: " . $e->getMessage());
            return false;
        }
    }

    public function sendTemplate(string $to, string $templateName, string $languageCode, array $parameters = []): bool
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode,
                ],
            ],
        ];

        if (!empty($parameters)) {
            $payload['template']['components'][] = [
                'type' => 'body',
                'parameters' => collect($parameters)->map(fn($value, $key) => [
                    'type' => 'text',
                    'parameter_name' => $key,
                    'text' => (string) $value,
                ])->values()->toArray(),
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/messages", $payload);

            if ($response->successful()) {
                Log::info("Template {$templateName} sent to {$to}.");
                return true;
            }

            Log::error("Error sending template {$templateName} to {$to}: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::critical("Exception sending template {$templateName} to {$to}: " . $e->getMessage());
            return false;
        }
    }
}
