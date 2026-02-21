<?php

use App\Models\Applicant;
use App\Services\WhatsappApiNotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // Nota de Vero real JAJA: Se cargan en chunks de 100 para no quedarse sin memoria
    Applicant::where('process_status', 'in_progress')
        ->chunk(100, function ($applicants) {
            $notificationService = new WhatsappApiNotificationService();

            $applicants->load('conversation.latestMessage');

            foreach ($applicants as $applicant) {
                $conversation = $applicant->conversation;

                if (!$conversation) continue;

                $lastMessage = $conversation->latestMessage;

                if (!$lastMessage || $lastMessage->role === 'user') continue;

                $hoursSinceLastMessage = $lastMessage->created_at->diffInHours(now());

                if ($applicant->reminder_level === 0 && $hoursSinceLastMessage >= 23) {
                    $notificationService->sendCustomMessage(
                        $applicant,
                        'Hola! Somos parte del equipo de Casas de Esperanza, seguimos esperando tu respuesta para continuar con tu proceso. Si tienes alguna duda o necesitas ayuda, no dudes en escribirnos. ¡Estamos aquí para apoyarte! ❤️',
                        'primer_recontacto'
                    );

                    $applicant->update(['reminder_level' => 1]);
                    continue;
                }

                if ($applicant->reminder_level === 1 && $hoursSinceLastMessage >= 48) {
                    $notificationService->sendCustomMessage(
                        $applicant,
                        'Hola! Somos parte del equipo de Casas de Esperanza, te mandamos este mensaje para recordarte que tienes pendiente terminar con tu aplicación, tienes 3 días para continuar tu proceso de aplicación antes de que sea cancelada, esperamos tu respuesta ❤️',
                        'recontacto_final',
                    );

                    $applicant->update(['reminder_level' => 2]);
                    continue;
                }

                if ($applicant->reminder_level === 2 && $hoursSinceLastMessage >= 72) {
                    $applicant->update([
                        'process_status' => 'canceled',
                        'rejection_reason' => 'cancelled_due_to_inactivity'
                    ]);
                    continue;
                }
            }
        });
})->hourly();

Schedule::call(function () {
    Applicant::where('process_status', 'in_progress')
        ->chunk(100, function ($applicants) {

            $applicants->load('conversation.latestMessage');

            foreach ($applicants as $applicant) {
                $conversation = $applicant->conversation;

                if (!$conversation || !$conversation->latestMessage) continue;

                $lastMessage = $conversation->latestMessage;

                if ($lastMessage->role !== 'user') continue;

                $minutesSinceLastMessage = $lastMessage->created_at->diffInMinutes(now());

                if ($minutesSinceLastMessage >= 60) {
                    $applicant->update([
                        'process_status' => 'requires_revision',
                    ]);
                }
            }
        });
})->hourly();
