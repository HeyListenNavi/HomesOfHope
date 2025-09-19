<?php

namespace App\Services;

use App\Models\Group;
use App\Services\EvolutionApiNotificationService;
use Illuminate\Support\Facades\Log;


class GroupActions
{
    /**
     * Envía un mensaje personalizado a todos los aplicantes de un grupo específico.
     *
     * @param Group $group El grupo de destino.
     * @param string $message El mensaje a enviar.
     * @return void
     */
    public static function sendCustomMessageToGroup(Group $group, string $message): void
    {
        Log::info("Enviando mensaje personalizado a todos los aplicantes del grupo con ID {$group->id}.");
        $notificationService = new EvolutionApiNotificationService();

        $applicants = $group->applicants;

        if ($applicants->isEmpty()) {
            Log::warning("No se encontraron aplicantes en el grupo con ID {$group->id}.");
            return;
        }

        foreach ($applicants as $applicant) {
            $notificationService->sendCustomMessage($applicant, $message);
        }

        Log::info("Mensaje personalizado enviado a los " . $applicants->count() . " aplicantes del grupo {$group->name}.");
    }

    /**
     * Reenvía el mensaje de bienvenida del grupo a todos sus aplicantes.
     * El mensaje se obtiene del campo 'message' del modelo Group.
     *
     * @param Group $group El grupo de destino.
     * @return void
     */
    public static function reSendGroupMessage(Group $group): void
    {
        Log::info("Reenviando el mensaje del grupo con ID {$group->id} a todos sus aplicantes.");
        $notificationService = new EvolutionApiNotificationService();

        /* $groupMessage = $group->message;

        if (empty($groupMessage)) {
            Log::warning("El grupo con ID {$group->id} no tiene un mensaje definido. No se puede reenviar.");
            return;
        } */

        $applicants = $group->applicants;

        if ($applicants->isEmpty()) {
            Log::warning("No se encontraron aplicantes en el grupo con ID {$group->id}.");
            return;
        }

        foreach ($applicants as $applicant) {
            $notificationService->sendSuccessInfo($applicant);
        }

        Log::info("Mensaje del grupo reenviado a los " . $applicants->count() . " aplicantes del grupo {$group->name}.");
    }
}
