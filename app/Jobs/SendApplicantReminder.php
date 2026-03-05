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

        if (!$applicant) return;

        $applicant->load('conversation.latestMessage');
        $conversation = $applicant->conversation;

        if (!$conversation || !$conversation->latestMessage) return;

        $latestUserMessage = $conversation->messages()->where('role', 'user')->latest('created_at')->first();
        $referenceTime = $latestUserMessage ? $latestUserMessage->created_at : $applicant->created_at;

        $hoursSinceLastReminder = $applicant->last_reminded_at ? $applicant->last_reminded_at->diffInHours(now()) : 0;

        if ($applicant->reminder_level === 0 && $hoursSinceLastMessage >= 22) {
            $notificationService->sendCustomMessage($applicant, 'Hola! Somos parte del equipo de Casas de Esperanza, seguimos esperando tu respuesta para continuar con tu proceso. Si tienes alguna duda o necesitas ayuda, no dudes en escribirnos. ¡Estamos aquí para apoyarte! ❤️', 'primer_recontacto');

            $applicant->update([
                'reminder_level' => 1,
                'last_reminded_at' => now()
            ]);
            return;
        }

        if ($applicant->reminder_level === 1 && $hoursSinceLastReminder >= 48) {
            $notificationService->sendCustomMessage($applicant, 'Hola! Somos parte del equipo de Casas de Esperanza, te mandamos este mensaje para recordarte que tienes pendiente terminar con tu aplicación, tienes 3 días para continuar tu proceso de aplicación antes de que sea cancelada, esperamos tu respuesta ❤️', 'recontacto_final');

            $applicant->update([
                'reminder_level' => 2,
                'last_reminded_at' => now()
            ]);
            return;
        }

        if ($applicant->reminder_level === 2 && $hoursSinceLastReminder >= 72) {
            $applicant->update([
                'process_status' => 'canceled'
            ]);
            return;
        }
    }
}
