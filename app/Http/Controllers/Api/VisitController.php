<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /**
     * List visits. Supports filtering by family_profile_id and status.
     */
    public function index(Request $request)
    {
        $query = Visit::with(['attendant:id,name', 'familyProfile:id,family_name']);

        if ($request->has('family_profile_id')) {
            $query->where('family_profile_id', $request->family_profile_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Ordenar: Próximas visitas primero, luego las recientes
        // O simplemente cronológico descendente
        $query->orderBy('scheduled_at', 'desc');

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'family_profile_id' => 'required|exists:family_profiles,id',
            'scheduled_at' => 'required|date',
            'location_type' => 'nullable|string',
            'status' => 'required|string|in:scheduled,completed,canceled',
            // Si no envían attended_by, asignamos al usuario actual
            'attended_by' => 'nullable|exists:users,id', 
        ]);

        // Default al usuario autenticado si no se especifica otro
        if (empty($validated['attended_by'])) {
            $validated['attended_by'] = $request->user()->id;
        }

        $visit = Visit::create($validated);

        return response()->json([
            'message' => 'Visit scheduled successfully',
            'data' => $visit
        ], 201);
    }

    public function show(string $id)
    {
        // Cargamos relaciones útiles para la vista de detalle
        $visit = Visit::with(['familyProfile', 'attendant', 'notes', 'documents'])
            ->findOrFail($id);

        return response()->json($visit);
    }

    public function update(Request $request, string $id)
    {
        $visit = Visit::findOrFail($id);

        $validated = $request->validate([
            'scheduled_at' => 'sometimes|date',
            'completed_at' => 'nullable|date',
            'status' => 'sometimes|string|in:scheduled,completed,canceled,rescheduled',
            'location_type' => 'nullable|string',
            'outcome_summary' => 'nullable|string',
            'attended_by' => 'sometimes|exists:users,id',
        ]);

        $visit->update($validated);

        return response()->json([
            'message' => 'Visit updated successfully',
            'data' => $visit
        ]);
    }

    public function destroy(string $id)
    {
        $visit = Visit::findOrFail($id);
        $visit->delete();

        return response()->json(['message' => 'Visit deleted successfully']);
    }
}