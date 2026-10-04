<?php

namespace App\Http\Controllers\Api;

use App\Enums\VisitLocationType;
use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class VisitController extends Controller
{
    /**
     * List visits. Supports filtering by family_profile_id and status.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Visit::class);

        $validated = $request->validate([
            'family_profile_id' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::enum(VisitStatus::class)],
            'date' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'min_date' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $query = Visit::with([
            'attendants:id,name',
            'familyProfile:id,family_name,home_address_link,land_address_link,responsible_member_id',
            'familyProfile.responsibleMember:id,family_profile_id,phone',
            'notes',
        ]);
        if (isset($validated['family_profile_id'])) {
            $query->where('family_profile_id', $validated['family_profile_id']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['date'])) {
            $query->whereDate('scheduled_at', $validated['date']);
        }

        if (isset($validated['min_date'])) {
            $query->whereDate('scheduled_at', '>=', $validated['min_date']);
        }

        $query->orderBy('scheduled_at', 'desc');

        return $query->paginate(20);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Visit::class);

        $validated = $request->validate([
            'family_profile_id' => 'required|exists:family_profiles,id',
            'scheduled_at' => 'required|date',
            'location_type' => ['nullable', Rule::enum(VisitLocationType::class)],
            'status' => ['required', Rule::enum(VisitStatus::class)],
            'attendants' => 'nullable|array',
            'attendants.*' => 'exists:users,id',
        ]);

        $attendants = $validated['attendants'] ?? [$request->user()->id];

        $visit = Visit::create(Arr::except($validated, 'attendants'));
        $visit->attendants()->sync($attendants);

        return response()->json([
            'message' => 'Visit scheduled successfully',
            'data' => $visit->load('attendants'),
        ], 201);
    }

    public function show(Visit $visit)
    {
        $this->authorize('view', $visit);

        return $visit->load(['familyProfile.responsibleMember', 'attendants', 'notes.author', 'evidences', 'tasks']);
    }

    public function update(Request $request, Visit $visit)
    {
        $this->authorize('update', $visit);

        $validated = $request->validate([
            'scheduled_at' => 'sometimes|date',
            'completed_at' => 'nullable|date',
            'status' => ['sometimes', Rule::enum(VisitStatus::class)],
            'location_type' => ['nullable', Rule::enum(VisitLocationType::class)],
            'outcome_summary' => 'nullable|string',
            'attendants' => 'nullable|array',
            'attendants.*' => 'exists:users,id',
        ]);

        $visit->update(Arr::except($validated, 'attendants'));

        if (isset($validated['attendants'])) {
            $visit->attendants()->sync($validated['attendants']);
        }

        return response()->json([
            'message' => 'Visit updated successfully',
            'data' => $visit->load('attendants'),
        ]);
    }

    public function destroy(Visit $visit)
    {
        $this->authorize('delete', $visit);

        $visit->delete();

        return response()->json(['message' => 'Visit deleted successfully']);
    }
}
