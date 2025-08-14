<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfirmationController extends Controller
{
    /**
     * Muestra el formulario de confirmación.
     */
    public function showForm(Applicant $applicant)
    {
        // Asegúrate de que el solicitante esté aprobado y pendiente de confirmación
        if (!$applicant->is_approved || $applicant->confirmation_status !== 'pending') {
            return redirect('/')->with('error', 'Su estatus de confirmación no es válido.');
        }

        $currentGroup = $applicant->group;
        $availableGroups = Group::where('current_members_count', '<', DB::raw('capacity'))
                                 ->where('id', '!=', $currentGroup->id)
                                 ->whereNotNull('date')
                                 ->get();

        return view('confirmation.form', compact('applicant', 'currentGroup', 'availableGroups'));
    }

    /**
     * Procesa la confirmación o el cambio de grupo.
     */
    public function confirmGroup(Request $request, Applicant $applicant)
    {
        // Validar la solicitud
        $request->validate([
            'new_group_id' => 'nullable|exists:groups,id',
        ]);

        return DB::transaction(function () use ($request, $applicant) {
            $currentGroup = $applicant->group;
            $newGroupId = $request->input('new_group_id');
            $message = 'Su lugar ha sido confirmado exitosamente.';
            $hasChangedGroup = false;

            // Lógica para cambiar de grupo
            if ($newGroupId && (int)$newGroupId !== $currentGroup->id) {
                $newGroup = Group::find($newGroupId);

                if ($newGroup && $newGroup->current_members_count < $newGroup->capacity) {
                    $currentGroup->decrement('current_members_count');

                    $applicant->group_id = $newGroup->id;
                    $applicant->confirmation_status = 'confirmed';
                    $applicant->save();
                    
                    $newGroup->increment('current_members_count');

                    $message = 'Ha cambiado de grupo y su lugar ha sido confirmado.';
                    $hasChangedGroup = true;
                } else {
                    return back()->with('error', 'El grupo seleccionado ya no tiene cupo. Por favor, intente de nuevo.');
                }
            } else {
                // El solicitante confirmó su grupo actual
                if ($applicant->confirmation_status === 'pending') {
                    $applicant->confirmation_status = 'confirmed';
                    $applicant->save();
                    $currentGroup->increment('current_members_count');
                }
            }
            
            return redirect()->route('confirmation.success')->with('success', $message);
        });
    }

    /**
     * Muestra una página de éxito.
     */
    public function showSuccess()
    {
        return view('confirmation.success');
    }
}