<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendToN8nJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $payload;

    public $tries = 4; // 1 inicial + 3 retries

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function backoff(): array
    {
        return [60, 180, 540];
    }

    public function handle(): void
    {
        Log::info('Forwarding webhook to n8n');

        $response = Http::timeout(10)
            ->retry(0, 0) // dejamos el retry a Laravel queue
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post(
                config('services.n8n.webhook_url'),
                $this->payload
            );

        if (!$response->successful()) {

            Log::error('n8n error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception('n8n did not return success');
        }

        Log::info('n8n success');
    }

    public function failed(\Throwable $exception)
    {
        Log::critical('Webhook permanently failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
