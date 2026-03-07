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
    public function __construct(public int $applicantId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsappApiNotificationService $notificationService): void
    {
        $applicant = Applicant::where('process_status', 'in_progress')->find($this->applicantId);

        if (!$this->shouldProcessReminder($applicant)) {
            return;
        }

        $latestUserMessage = $applicant->conversation->messages()->where('role', 'user')->latest('created_at')->first();
        $referenceTime = $latestUserMessage ? $latestUserMessage->created_at : $applicant->created_at;

        $hoursSinceMessage = $referenceTime->diffInHours(now());
        $hoursSinceLastReminder = $applicant->last_reminded_at ? $applicant->last_reminded_at->diffInHours(now()) : 0;

        if ($hoursSinceMessage >= 22) {
            $notificationService->sendCustomMessage(
                $applicant,
                'Hola! Somos parte del equipo de Casas de Esperanza, seguimos esperando tu respuesta para continuar con tu proceso...',
                'primer_recontacto'
            );
            $this->updateReminder($applicant, 1);
            return;
        }

        if ($hoursSinceLastReminder >= 48) {
            $notificationService->sendCustomMessage(
                $applicant,
                'Hola! Somos parte del equipo de Casas de Esperanza, te mandamos este mensaje para recordarte que tienes pendiente terminar con tu aplicación...',
                'recontacto_final'
            );
            $this->updateReminder($applicant, 2);
            return;
        }

        if ($hoursSinceLastReminder >= 72) {
            $applicant->update(['process_status' => 'canceled']);
            return;
        }
    }

    private function shouldProcessReminder(?Applicant $applicant): bool
    {
        if (!$applicant) return false;

        $applicant->load('conversation.latestMessage');
        $conversation = $applicant->conversation;

        if (!$conversation || !$conversation->latestMessage) {
            return false;
        }

        return true;
    }

    private function updateReminder(Applicant $applicant, int $nextLevel): void
    {
        $applicant->update([
            'reminder_level' => $nextLevel,
            'last_reminded_at' => now(),
        ]);
    }
}
