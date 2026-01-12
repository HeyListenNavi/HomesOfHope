<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyMember;
use Illuminate\Http\Request;

class FamilyMemberController extends Controller
{
    public function index(Request $request)
    {
        // Opcional: Filtrar por family_profile_id si viene en el query param
        $query = FamilyMember::query();

        if ($request->has('family_profile_id')) {
            $query->where('family_profile_id', $request->family_profile_id);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'family_profile_id' => 'required|exists:family_profiles,id',
            'name' => 'required|string',
            'paternal_surname' => 'required|string',
            'maternal_surname' => 'nullable|string',
            'birth_date' => 'required|date',
            'curp' => 'nullable|string|unique:family_members,curp',
            'relationship' => 'required|string',
            'is_responsible' => 'boolean',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'occupation' => 'nullable|string',
            'medical_notes' => 'nullable|string',
        ]);

        $member = FamilyMember::create($validated);

        // Si este miembro se marca como responsable, actualizar el perfil padre
        if ($member->is_responsible) {
            $member->familyProfile()->update(['responsible_member_id' => $member->id]);
        }

        return response()->json([
            'message' => 'Family Member created successfully',
            'data' => $member
        ], 201);
    }

    public function show(string $id)
    {
        $member = FamilyMember::with('familyProfile')->findOrFail($id);
        return response()->json($member);
    }

    public function update(Request $request, string $id)
    {
        $member = FamilyMember::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'paternal_surname' => 'sometimes|string',
            'maternal_surname' => 'nullable|string',
            'birth_date' => 'sometimes|date',
            'curp' => 'nullable|string|unique:family_members,curp,'.$member->id,
            'relationship' => 'sometimes|string',
            'is_responsible' => 'boolean',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'occupation' => 'nullable|string',
            'medical_notes' => 'nullable|string',
        ]);

        $member->update($validated);

        // Lógica para actualizar responsable en perfil si cambió el flag
        if (isset($validated['is_responsible']) && $validated['is_responsible']) {
            $member->familyProfile()->update(['responsible_member_id' => $member->id]);
        }

        return response()->json([
            'message' => 'Family Member updated successfully',
            'data' => $member
        ]);
    }

    public function destroy(string $id)
    {
        $member = FamilyMember::findOrFail($id);
        $member->delete();

        return response()->json(['message' => 'Family Member deleted successfully']);
    }
}