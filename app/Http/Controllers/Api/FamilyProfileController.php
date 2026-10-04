<?php

namespace App\Http\Controllers\Api;

use App\Enums\Currency;
use App\Enums\FamilyStatus;
use App\Enums\LandService;
use App\Enums\LandSize;
use App\Http\Controllers\Controller;
use App\Models\FamilyProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $this->authorize('viewAny', FamilyProfile::class);

        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1'],
            'family_name' => ['sometimes', 'string'],
            'curp' => ['sometimes', 'string'],
            'responsible_name' => ['sometimes', 'string'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $profiles = FamilyProfile::with('responsibleMember')
            ->when($validated['family_name'] ?? null, fn ($q, $val) => $q->where('family_name', 'like', "%{$val}%"))
            ->when($validated['curp'] ?? null, fn ($q, $val) => $q->whereHas('responsibleMember', fn ($sq) => $sq->where('curp', 'like', "%{$val}%")))
            ->when($validated['responsible_name'] ?? null, fn ($q, $val) => $q->whereHas('responsibleMember', fn ($sq) => $sq->where('name', 'like', "%{$val}%")
                ->orWhere('paternal_surname', 'like', "%{$val}%")
                ->orWhere('maternal_surname', 'like', "%{$val}%")
            ))
            ->latest()
            ->paginate($validated['limit'] ?? 15);

        return $profiles;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', FamilyProfile::class);

        $validated = $request->validate([
            'family_name' => 'required|string|max:255',
            'status' => ['required', Rule::enum(FamilyStatus::class)],
            'interviewer_name' => 'nullable|string|max:255',
            'opened_at' => 'required|date',
            'reason' => 'nullable|string',
            'general_observations' => 'nullable|string',
            'lives_on_land' => 'required|boolean',
            'has_addictions' => 'nullable|boolean',
            'addictions_details' => 'nullable|string',
            'construction_notified' => 'nullable|boolean',

            'home_city' => 'nullable|string|max:255',
            'home_colony' => 'nullable|string|max:255',
            'home_address' => 'nullable|string|max:255',
            'home_address_link' => 'nullable|string|max:500',
            'home_latitude' => 'nullable|numeric|between:-90,90',
            'home_longitude' => 'nullable|numeric|between:-180,180',
            'home_ownership_time' => 'nullable|string|max:255',
            'home_owner_name' => 'nullable|string|max:255',
            'home_monthly_rent' => 'nullable|numeric|min:0',
            'home_monthly_rent_currency' => ['nullable', Rule::enum(Currency::class)],
            'home_has_receipts' => 'nullable|boolean',
            'house_description' => 'nullable|string',

            'land_city' => 'nullable|string|max:255',
            'land_colony' => 'nullable|string|max:255',
            'land_address' => 'nullable|string|max:255',
            'land_address_link' => 'nullable|string|max:500',
            'land_latitude' => 'nullable|numeric|between:-90,90',
            'land_longitude' => 'nullable|numeric|between:-180,180',
            'land_ownership_time' => 'nullable|string|max:255',
            'land_total_cost' => 'nullable|numeric|min:0',
            'land_down_payment' => 'nullable|numeric|min:0',
            'land_monthly_payment' => 'nullable|numeric|min:0',
            'land_currency' => ['nullable', Rule::enum(Currency::class)],
            'land_last_payment_date' => 'nullable|date',
            'land_is_up_to_date' => 'nullable|boolean',
            'land_is_flat' => 'nullable|boolean',
            'land_size' => ['nullable', Rule::enum(LandSize::class)],
            'land_services' => 'nullable|array',
            'land_services.*' => ['string', Rule::enum(LandService::class)],

            'building_start_date' => 'nullable|date',
            'building_finish_date' => 'nullable|date',
            'building_team' => 'nullable|string|max:255',
            'building_team_color' => 'nullable|string|max:255',

            'responsible_member_id' => 'nullable|exists:family_members,id',
        ]);

        $profile = FamilyProfile::create($validated);

        return response()->json([
            'message' => 'Family Profile created successfully',
            'data' => $profile,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FamilyProfile $familyProfile)
    {
        $this->authorize('view', $familyProfile);

        return $familyProfile->load(['members', 'responsibleMember', 'documents']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FamilyProfile $familyProfile)
    {
        $this->authorize('update', $familyProfile);

        $validated = $request->validate([
            'family_name' => 'sometimes|string|max:255',
            'status' => ['sometimes', Rule::enum(FamilyStatus::class)],
            'interviewer_name' => 'nullable|string|max:255',
            'opened_at' => 'sometimes|date',
            'reason' => 'nullable|string',
            'general_observations' => 'nullable|string',
            'lives_on_land' => 'sometimes|boolean',
            'has_addictions' => 'nullable|boolean',
            'addictions_details' => 'nullable|string',
            'construction_notified' => 'nullable|boolean',

            'home_city' => 'nullable|string|max:255',
            'home_colony' => 'nullable|string|max:255',
            'home_address' => 'nullable|string|max:255',
            'home_address_link' => 'nullable|string|max:500',
            'home_latitude' => 'nullable|numeric|between:-90,90',
            'home_longitude' => 'nullable|numeric|between:-180,180',
            'home_ownership_time' => 'nullable|string|max:255',
            'home_owner_name' => 'nullable|string|max:255',
            'home_monthly_rent' => 'nullable|numeric|min:0',
            'home_monthly_rent_currency' => ['nullable', Rule::enum(Currency::class)],
            'home_has_receipts' => 'nullable|boolean',
            'house_description' => 'nullable|string',

            'land_city' => 'nullable|string|max:255',
            'land_colony' => 'nullable|string|max:255',
            'land_address' => 'nullable|string|max:255',
            'land_address_link' => 'nullable|string|max:500',
            'land_latitude' => 'nullable|numeric|between:-90,90',
            'land_longitude' => 'nullable|numeric|between:-180,180',
            'land_ownership_time' => 'nullable|string|max:255',
            'land_total_cost' => 'nullable|numeric|min:0',
            'land_down_payment' => 'nullable|numeric|min:0',
            'land_monthly_payment' => 'nullable|numeric|min:0',
            'land_currency' => ['nullable', Rule::enum(Currency::class)],
            'land_last_payment_date' => 'nullable|date',
            'land_is_up_to_date' => 'nullable|boolean',
            'land_is_flat' => 'nullable|boolean',
            'land_size' => ['nullable', Rule::enum(LandSize::class)],
            'land_services' => 'nullable|array',
            'land_services.*' => ['string', Rule::enum(LandService::class)],

            'building_start_date' => 'nullable|date',
            'building_finish_date' => 'nullable|date',
            'building_team' => 'nullable|string|max:255',
            'building_team_color' => 'nullable|string|max:255',

            'responsible_member_id' => 'nullable|exists:family_members,id',
        ]);

        $familyProfile->update($validated);

        return response()->json([
            'message' => 'Family Profile updated successfully',
            'data' => $familyProfile,
        ]);
    }

    public function storePhoto(Request $request, FamilyProfile $familyProfile)
    {
        $this->authorize('update', $familyProfile);

        $validated = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,heic', 'max:10240'],
        ]);

        $path = $validated['file']->store('documents', 'r2');

        $familyPhotoPath = $path;

        $familyProfile->update(['family_photo_path' => $familyPhotoPath]);

        return response()->json([
            'message' => 'Family photo uploaded successfully',
            'data' => $familyProfile->load(['members', 'responsibleMember', 'documents']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FamilyProfile $familyProfile)
    {
        $this->authorize('delete', $familyProfile);

        // Opcional: Validar si tiene relaciones activas antes de borrar
        $familyProfile->delete();

        return response()->json([
            'message' => 'Family Profile deleted successfully',
        ], 200);
    }
}
