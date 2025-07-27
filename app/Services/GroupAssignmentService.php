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
            // Buscamos un grupo con espacio disponible.
            // lockForUpdate() es crucial aquí para evitar condiciones de carrera
            // si múltiples solicitantes son procesados concurrentemente.
            $group = Group::where('current_members_count', '<', DB::raw('capacity'))
                        ->orderBy('current_members_count', 'asc') // Prioriza grupos con menos miembros
                        ->orderBy('id', 'asc') // Desempate por ID
                        ->lockForUpdate() // Bloquea la fila para la actualización
                        ->first();

            // Si no hay ningún grupo con espacio, creamos uno nuevo.
            if (!$group) {
                $lastGroup = Group::orderBy('id', 'desc')->first();
                // Determina el número del siguiente grupo (ej. "Grupo 1", "Grupo 2", etc.)
                $nextGroupNumber = $lastGroup ? (int) filter_var($lastGroup->name, FILTER_SANITIZE_NUMBER_INT) + 1 : 1;

                $group = Group::create([
                    'name' => 'Grupo ' . $nextGroupNumber,
                    'capacity' => 25, // O la capacidad predeterminada que decidas
                    'current_members_count' => 0 // Nuevo grupo inicia con 0 miembros
                ]);
            }

            // Asigna el ID del grupo al solicitante
            $applicant->group_id = $group->id;
            // El applicant->save() se hará en el controlador después de este servicio

            // Incrementa el contador de miembros del grupo.
            // Usamos increment() directamente para que se maneje la base de datos de forma segura.
            $group->increment('current_members_count');

            return $group;
        });
    }
}
