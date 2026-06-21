<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicantStatus;
use App\Http\Controllers\Controller;
use App\Models\Applicant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Clase controladora para gestionar las actualizaciones manuales de solicitantes.
 *
 * Este controlador es ideal para ser usado por una interfaz de administración
 * como Filament.
 */
class BotApplicantManualController extends Controller
{
    /**
     * Actualiza el estado de un solicitante manualmente.
     *
     * @param  int  $applicantId  El ID del solicitante.
     * @return JsonResponse
     */
    public function updateManually(Request $request, int $applicantId)
    {
        // Este endpoint es para uso exclusivo de Filament
        $applicant = Applicant::findOrFail($applicantId);

        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'user_response' => 'nullable|string',
            'current_stage_id' => 'nullable|exists:stages,id',
            'current_question_id' => 'nullable|exists:questions,id',
            'process_status' => ['nullable', Rule::enum(ApplicantStatus::class)],
        ]);

        // Actualiza la respuesta en el historial
        $response = $applicant->responses()->where('question_id', $validated['question_id'])->firstOrFail();
        $response->update(['user_response' => $validated['user_response']]);

        // Actualiza el estado si se pasa en el request
        if (isset($validated['current_stage_id'])) {
            $applicant->current_stage_id = $validated['current_stage_id'];
        }
        if (isset($validated['current_question_id'])) {
            $applicant->current_question_id = $validated['current_question_id'];
        }
        if (isset($validated['process_status'])) {
            $applicant->process_status = $validated['process_status'];
        }

        $applicant->save();

        return response()->json(['status' => 'success', 'applicant' => $applicant]);
    }
}
