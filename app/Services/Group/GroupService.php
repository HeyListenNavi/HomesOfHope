<?php

namespace App\Services\Group;

use App\Models\Applicant;
use App\Models\Group;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

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

        $invitationUrl = URL::temporarySignedRoute(
            'invitation.show',
            $group->date_time->addDay(),
            ['applicant' => $applicant]
        );

        $day = $group->date_time->translatedFormat('l d M, Y');
        $time = $group->date_time->translatedFormat('h:i A');
        $address = $group->location;
        $address_link = $group->location_link;
        $invitation = "En este link puedes ver todos los detalles de tu entrevista y el QR de asistencia: " . $invitationUrl;

        $message = "Felicidades! La cita para tu entrevista presencial fue registrada con exito.\n".
            "Por favor recuerda la siguiente informacion:\n".
            "Tu cita es el dia: {$day}\n".
            "A las: {$time}\n".
            "Con direccion: ${address}\n".
            "Ubicacion: ${address_link}\n\n".

            "Aqui estan mas detalles sobre tu entrevista\n".
            "${invitation}\n".
            "No olvides leer la informacion, es importante para realizar tu entrevista correctamente\n";

        $this->whatsappService->send($applicant, $message, 'enviar_informacion_de_entrevista', [
            'dia' => $day,
            'hora' => $time,
            'direccion' => $address,
            'ubicacion' => $address_link,
            'detalles_extra' => $invitation,
        ]);
    }

    public function sendInterviewReminder(Applicant $applicant, int $daysRemaining): void
    {
        $group = $applicant->group;

        $invitationUrl = URL::temporarySignedRoute(
            'invitation.show',
            $group->date_time->addDay(),
            ['applicant' => $applicant]
        );

        $daysText = match (true) {
            $daysRemaining === 1 => 'mañana',
            default => "en {$daysRemaining} días",
        };

        $dateTime = $group->date_time->translatedFormat('l d M, Y') . "a las " . $group->date_time->translatedFormat('h:i A');
        $address = $group->location;
        $invitation = "En este link puedes ver todos los detalles de tu entrevista y el QR de asistencia: " . $invitationUrl;


        $message = "Hola! Somos del equipo de Casas de Esperanza, nos gustaría recordarte que tu fecha de entrevista es el día {$dateTime}. La entrevista sera en ${address}\n".
            "Aquí hay mas detalles sobre tu entrevista:\n".

            "{$invitation}\n".

            "No olvides leer la información, es importante para realizar tu entrevista correctamente.\n".
            "En caso de que no vayas a poder asistir solo mandanos un mensaje aquí diciendo \"No podre asistir\" y nuestro asistente virtual se encargara de permitirte elegir otra fecha.";

        $this->whatsappService->send($applicant, $message, 'recordatorio_grupo', [
            'fecha' => $dateTime,
            'direccion' => $address,
            'detalles_extra' => $invitation,
        ]);
    }
}
