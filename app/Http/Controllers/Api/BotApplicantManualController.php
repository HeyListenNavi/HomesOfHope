<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * @param \Illuminate\Http\Request $request
     * @param int $applicantId El ID del solicitante.
     * @return \Illuminate\Http\JsonResponse
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
            'process_status' => 'nullable|string|in:in_progress,approved,rejected,requires_revision,canceled',
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
