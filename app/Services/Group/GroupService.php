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

    public function sendInterviewReminder(Applicant $applicant): void
    {
        $group = $applicant->group;
        
        $groupDateTime = $group->date_time->translatedFormat('l d M, Y') . ' a las ' . $group->date_time->translatedFormat('h:i A');
        $groupLocation = $group->location;
        $groupMessage = $group->message;

        $message = "Hola! Somos del equipo de Casas de Esperanza, nos gustaría recordarte que tu fecha de entrevista es el día " . $groupDateTime . ". La entrevista sera en " . $groupLocation . "\n" .
            "Aquí hay mas detalles sobre tu entrevista:\n" .
            $groupMessage . "\n\n" .
            "No olvides leer la información, es importante para realizar tu entrevista correctamente\n" .
            "En caso de que no vayas a poder asistir solo mandanos un mensaje aquí diciendo \"No podre asistir\" y nuestro asistente virtual se encargara de permitirte elegir otra fecha.";

        $this->whatsappService->send($applicant, $message, 'recordatorio_grupo', [
            'fecha' => $groupDateTime,
            'direccion' => $groupLocation,
            'detalles_extra' => $groupMessage,
        ]);
    }
}
