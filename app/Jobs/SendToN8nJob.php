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

    public $message;

    public $tries = 4; // 1 inicial + 3 retries

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function backoff()
    {
        return [60, 180, 540];
    }

    public function handle(): void
    {
        Log::info('Dispatching to n8n', [
            'id' => $this->message['message_id'],
            'type' => $this->message['type'],
        ]);

        $response = Http::timeout(10)
            ->retry(0, 0) // dejamos el retry a Laravel queue
            ->post(config('services.n8n.webhook_url'), $this->message);

        if (!$response->successful()) {

            Log::error('n8n error', [
                'id' => $this->message['message_id'],
                'status' => $response->status(),
            ]);

            throw new \Exception('n8n did not return success');
        }

        Log::info('n8n success', [
            'id' => $this->message['message_id'],
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::critical('Message permanently failed', [
            'id' => $this->message['message_id'],
            'error' => $exception->getMessage(),
        ]);
    }
}

