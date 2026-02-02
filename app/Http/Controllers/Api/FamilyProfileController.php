<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FamilyProfileController extends Controller
{
    /**
     * Listar perfiles de familia con filtros avanzados y relaciones.
     *
     * Ejemplos de uso:
     *
     * - General:
     * GET /api/family-profiles
     *
     * - Paginación (50 por página):
     * GET /api/family-profiles?limit=50
     *
     * - Por Nombre de Familia:
     * GET /api/family-profiles?family_name=Gomez
     *
     * - Por Responsable (Nombre o Apellidos):
     * GET /api/family-profiles?responsible_name=Juan
     *
     * - Por CURP:
     * GET /api/family-profiles?curp=ABCD123456
     *
     * - Combinado:
     * GET /api/family-profiles?family_name=Gomez&responsible_name=Juan
     */
    public function index(Request $request)
    {
        $profiles = FamilyProfile::with('responsibleMember')
            ->when($request->family_name, fn($q, $val) => 
                $q->where('family_name', 'like', "%{$val}%")
            )
            ->when($request->curp, fn($q, $val) => 
                $q->whereHas('responsibleMember', fn($sq) => 
                    $sq->where('curp', 'like', "%{$val}%")
                )
            )
            ->when($request->responsible_name, fn($q, $val) => 
                $q->whereHas('responsibleMember', fn($sq) => 
                    $sq->where('name', 'like', "%{$val}%")
                       ->orWhere('paternal_surname', 'like', "%{$val}%")
                       ->orWhere('maternal_surname', 'like', "%{$val}%")
                )
            )
            ->latest()
            ->paginate($request->input('limit', 15));

        return response()->json($profiles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'family_name' => 'required|string|max:255',
            'status' => 'required|string|in:prospect,active,in_follow_up,closed',
            'current_address' => 'required|array',
            'construction_address' => 'nullable|array',
            'opened_at' => 'required|date',
            'general_observations' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['family_name']) . '-' . uniqid();

        $profile = FamilyProfile::create($validated);

        return response()->json([
            'message' => 'Family Profile created successfully',
            'data' => $profile
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $profile = FamilyProfile::with(['members', 'responsibleMember']) 
            ->findOrFail($id);

        return response()->json($profile);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $profile = FamilyProfile::findOrFail($id);

        $validated = $request->validate([
            'family_name' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:prospect,active,in_follow_up,closed',
            'current_address' => 'sometimes|array',
            'construction_address' => 'nullable|array',
            'responsible_member_id' => 'nullable|exists:family_members,id', // Validación segura
            'opened_at' => 'sometimes|date',
            'closed_at' => 'nullable|date',
            'general_observations' => 'nullable|string',
        ]);

        if (isset($validated['family_name'])) {
            $validated['slug'] = Str::slug($validated['family_name']) . '-' . $profile->id;
        }

        $profile->update($validated);

        return response()->json([
            'message' => 'Family Profile updated successfully',
            'data' => $profile
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $profile = FamilyProfile::findOrFail($id);
        
        // Opcional: Validar si tiene relaciones activas antes de borrar
        $profile->delete();

        return response()->json([
            'message' => 'Family Profile deleted successfully'
        ], 200);
    }
}