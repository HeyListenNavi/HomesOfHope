<?php

namespace App\Jobs;

use App\Models\Applicant;
use App\Services\WhatsappApiNotificationService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendApplicantReminder implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(public Applicant $applicant)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsappApiNotificationService $whatsappService): void
    {
        $lastMessage = $this->applicant->conversation->messages()->latest()->first();

        if ($lastMessage && $lastMessage->created_at->diffInMinutes(now()) < 5)
            {
            return;
        }

        try {
            $whatsappService->sendCustomMessage($this->applicant, 'Hola! Somos del equipo de Casas de Esperanza, te contactamos para recordarte que aún no has terminado tu solicitud. Por favor, completa el proceso para que podamos ayudarte. Si necesitas ayuda, no dudes en contactarnos. ¡Gracias!');

            $whatsappService->sendCurrentQuestion($this->applicant);

            Log::info("Reminder sent to applicant: {$this->applicant->id}");
        } catch (Exception $e) {
            Log::error("Failed to remind applicant {$this->applicant->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
