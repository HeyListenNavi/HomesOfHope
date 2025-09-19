<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\EvolutionApiNotificationService;

class GroupSelectionController extends Controller
{
    /**
     * Muestra el formulario para que el aplicante elija un grupo.
     */
    public function showSelectionForm(Applicant $applicant)
    {
        // La ruta firmada ya protege contra manipulación de URL.
        // Verificamos el estado lógico del aplicante.
        if ( $applicant->group_id !== null) {
            return view('selection.invalid', [
                'message' => 'Este enlace no es válido o ya has seleccionado un grupo.'
            ]);
        }

        // Buscamos todos los grupos que tengan cupo disponible.
        $availableGroups = Group::where('current_members_count', '<', DB::raw('capacity'))
                               ->whereNotNull('date_time')
                               ->orderBy('date_time', 'asc')
                               ->get();

        return view('selection.form', compact('applicant', 'availableGroups'));
    }

    /**
     * Procesa la selección del grupo, lo asigna y confirma.
     */
    public function assignToGroup(Request $request, Applicant $applicant)
    {
        // Validación inicial
        if ( $applicant->group_id !== null) {
            return redirect()->route('selection.invalid')->with('error', 'Acción no permitida.');
        }

        $request->validate([
            'group_id' => 'required'
        ]);

        // Usamos una transacción para garantizar la integridad de los datos
        return DB::transaction(function () use ($request, $applicant) {
            $groupId = $request->input('group_id');
            
            // Bloqueamos la fila del grupo para evitar que dos personas
            // tomen el último lugar al mismo tiempo (race condition).
            $group = Group::where('id', $groupId)
                          ->where('current_members_count', '<', DB::raw('capacity'))
                          ->lockForUpdate()
                          ->first();

            if (!$group) {
                // Si el grupo se llenó mientras el usuario decidía.
                return back()->with('error', 'Lo sentimos, el grupo que seleccionaste se acaba de llenar. Por favor, elige otra opción.');
            }

            // Asignación final y definitiva
            $applicant->group_id = $group->id;
            $applicant->confirmation_status = 'confirmed';
            $applicant->save();
            
            // Incrementamos el contador del grupo
            $group->increment('current_members_count');

            $EvolutionApiNotificaiton = new EvolutionApiNotificationService();
            $EvolutionApiNotificaiton->sendSuccessInfo($applicant);

            return redirect()->route('selection.success')->with('success', '¡Excelente! Tu lugar en el grupo ha sido confirmado.');
        });
    }

    /**
     * Muestra una página de éxito genérica.
     */
    public function showSuccess()
    {
        return view('selection.success');
    }

    /**
     * Muestra una página para enlaces inválidos.
     */
    public function showInvalidLink()
    {
        return view('selection.invalid');
    }
}
