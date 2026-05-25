<?php

namespace App\Services\Group;

use App\Models\Group;
use App\Models\Applicant;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Support\Facades\Log;

class GroupService
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function sendCustomMessageToGroup(Group $group, string $message): void
    {
        Log::info("Sending custom message to all applicants in group ID {$group->id}.");

        $applicants = $group->applicants;

        foreach ($applicants as $applicant) {
            $this->sendAnnouncement($applicant, $message);
        }
    }

    public function sendAnnouncement(Applicant $applicant, string $announcement): void
    {
        $this->whatsappService->send($applicant, $announcement, 'aviso_grupo', [
            'fecha_entrevista' => $applicant->group->date_time->translatedFormat('l, d \d\e F \d\e\l Y'),
            'aviso' => $announcement,
        ]);
    }

    public function reSendGroupMessage(Group $group): void
    {
        Log::info("Resending group info for group ID {$group->id} to all its applicants.");

        $applicants = $group->applicants;

        foreach ($applicants as $applicant) {
            $this->sendInterviewDetails($applicant);
        }
    }

    public function sendInterviewDetails(Applicant $applicant): void
    {
        $group = $applicant->group;
        $message = "Felicidades! La cita para tu entrevista presencial fue registrada con exito.\n" .
            "Por favor recuerda la siguiente informacion:\n" .
            "Tu cita es el dia: " . $group->date_time->translatedFormat('l d M, Y') . "\n" .
            "A las: " . $group->date_time->translatedFormat('h:i A') . "\n" .
            "Con direccion: : " . $group->location . "\n" .
            "Ubicacion: " . $group->location_link . "\n";

        $message .= "No olvides leer la siguiente informacion importante: \n" . $group->message;

        $this->whatsappService->send($applicant, $message, 'enviar_informacion_de_entrevista', [
            'dia' => $group->date_time->translatedFormat('l d M, Y'),
            'hora' => $group->date_time->translatedFormat('h:i A'),
            'direccion' => $group->location,
            'ubicacion' => $group->location_link,
            'detalles_extra' => $group->message,
        ]);
    }
}
