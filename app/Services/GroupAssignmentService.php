<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Group;
use Illuminate\Support\Facades\DB;

class GroupAssignmentService
{
    /**
     * Asigna un solicitante aprobado a un grupo existente o nuevo.
     *
     * @param Applicant $applicant El modelo Applicant que necesita asignación.
     * @return Group El grupo al que fue asignado el solicitante.
     */
    public function assignApplicantToGroup(Applicant $applicant): Group
    {
        return DB::transaction(function () use ($applicant) {
            $group = Group::where('current_members_count', '<', DB::raw('capacity'))
                        ->whereNotNull('date') // Solo grupos que ya tienen una fecha asignada
                        ->orderBy('current_members_count', 'asc')
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->first();

            if (!$group) {
                // Si no hay grupos con espacio y fecha, puedes crear uno nuevo
                // o manejarlo como un caso especial. Aquí, se crea uno sin fecha.
                // La fecha se asignaría manualmente o mediante otro proceso.
                $group = Group::create([
                    'name' => 'Grupo ' . (Group::count() + 1),
                    'capacity' => 25,
                    'current_members_count' => 0,
                    // 'date' => null, // <-- Inicialmente sin fecha
                ]);
            }

            $applicant->group_id = $group->id;
            $applicant->confirmation_status = 'pending'; // Estado inicial de espera
            // Nota: no incrementamos el contador aquí.
            
            return $group;
        });
    }
}
