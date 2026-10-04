<?php

namespace App\Http\Controllers\Api;

use App\Enums\EducationLevel;
use App\Enums\MaritalStatus;
use App\Enums\Occupation;
use App\Enums\Relationship;
use App\Enums\Religion;
use App\Http\Controllers\Controller;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class FamilyMemberController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', FamilyMember::class);

        $validated = $request->validate([
            'family_profile_id' => ['sometimes', 'integer'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $query = FamilyMember::query();

        if (isset($validated['family_profile_id'])) {
            $query->where('family_profile_id', $validated['family_profile_id']);
        }

        return $query->paginate(20);
    }

    public function store(Request $request)
    {
        $this->authorize('create', FamilyMember::class);

        $validated = $request->validate([
            'family_profile_id' => 'required|exists:family_profiles,id',
            'name' => 'required|string',
            'paternal_surname' => 'required|string',
            'maternal_surname' => 'nullable|string',
            'birth_date' => 'required|date',
            'curp' => 'nullable|string|unique:family_members,curp',
            'relationship' => ['required', new Enum(Relationship::class)],
            'is_responsible' => 'boolean',
            'is_land_owner' => 'boolean',
            'phone' => 'nullable|string',
            'occupation' => ['nullable', new Enum(Occupation::class)],
            'marital_status' => ['nullable', new Enum(MaritalStatus::class)],
            'education_level' => ['nullable', new Enum(EducationLevel::class)],
            'education_grade' => 'nullable|integer',
            'weekly_income' => 'nullable|numeric',
            'religion' => ['nullable', new Enum(Religion::class)],
            'origin_state' => 'nullable|string|max:255',
            'origin_country' => 'nullable|string|max:255',
            'indigenous_language' => 'nullable|string',
            'speaks_indigenous_language' => 'boolean',
            'is_pregnant' => 'boolean',
            'pregnancy_months' => 'nullable|integer|min:1|max:9',
            'medical_notes' => 'nullable|string',
        ]);

        $member = FamilyMember::create($validated);

        // Si este miembro se marca como responsable, actualizar el perfil padre
        if ($member->is_responsible) {
            $member->familyProfile()->update(['responsible_member_id' => $member->id]);
        }

        return response()->json([
            'message' => 'Family Member created successfully',
            'data' => $member,
        ], 201);
    }

    public function show(FamilyMember $familyMember)
    {
        $this->authorize('view', $familyMember);

        return $familyMember->load(['familyProfile', 'documents']);
    }

    public function update(Request $request, FamilyMember $familyMember)
    {
        $this->authorize('update', $familyMember);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'paternal_surname' => 'sometimes|string',
            'maternal_surname' => 'nullable|string',
            'birth_date' => 'sometimes|date',
            'curp' => 'nullable|string|unique:family_members,curp,'.$familyMember->id,
            'relationship' => ['sometimes', new Enum(Relationship::class)],
            'is_responsible' => 'boolean',
            'is_land_owner' => 'boolean',
            'phone' => 'nullable|string',
            'occupation' => ['nullable', new Enum(Occupation::class)],
            'marital_status' => ['nullable', new Enum(MaritalStatus::class)],
            'education_level' => ['nullable', new Enum(EducationLevel::class)],
            'education_grade' => 'nullable|integer',
            'weekly_income' => 'nullable|numeric',
            'religion' => ['nullable', new Enum(Religion::class)],
            'origin_state' => 'nullable|string|max:255',
            'origin_country' => 'nullable|string|max:255',
            'indigenous_language' => 'nullable|string',
            'speaks_indigenous_language' => 'boolean',
            'is_pregnant' => 'boolean',
            'pregnancy_months' => 'nullable|integer|min:1|max:9',
            'medical_notes' => 'nullable|string',
        ]);

        $familyMember->update($validated);

        // Lógica para actualizar responsable en perfil si cambió el flag
        if (isset($validated['is_responsible']) && $validated['is_responsible']) {
            $familyMember->familyProfile()->update(['responsible_member_id' => $familyMember->id]);
        }

        return response()->json([
            'message' => 'Family Member updated successfully',
            'data' => $familyMember,
        ]);
    }

    public function destroy(FamilyMember $familyMember)
    {
        $this->authorize('delete', $familyMember);

        $familyMember->delete();

        return response()->json(['message' => 'Family Member deleted successfully']);
    }
}
