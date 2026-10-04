<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyProfile;
use App\Models\Testimony;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonyController extends Controller
{
    /**
     * List the testimonies recorded for a single family profile.
     *
     * The family profile is required: without it there is nothing to authorize
     * against, and the response would contain every testimony in the table.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'family_profile_id' => ['required', 'integer', 'exists:family_profiles,id'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $this->authorize('view', FamilyProfile::findOrFail($validated['family_profile_id']));

        $query = Testimony::with(['familyProfile:id,family_name', 'recorder:id,name'])
            ->where('family_profile_id', $validated['family_profile_id']);

        return response()->json($query->latest('recorded_at')->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'family_profile_id' => 'required|exists:family_profiles,id',
            'language' => 'required|string|in:es,en',
            'audio' => 'nullable|file|max:20480', // 20MB max
            'transcription' => 'nullable|string',
            'summary' => 'nullable|string',
            'recorded_at' => 'required|date',
        ]);

        $this->authorize('update', FamilyProfile::findOrFail($validated['family_profile_id']));

        $path = null;
        if ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('testimonies', 'r2');
        }

        $testimony = Testimony::create([
            'family_profile_id' => $validated['family_profile_id'],
            'language' => $validated['language'],
            'audio_path' => $path,
            'transcription' => $validated['transcription'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'recorded_at' => $validated['recorded_at'],
            'recorded_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Testimony recorded successfully',
            'data' => $testimony,
        ], 201);
    }

    public function show(Testimony $testimony)
    {
        $this->authorize('view', $testimony->familyProfile);

        $testimony->load(['familyProfile', 'recorder']);

        return response()->json($testimony);
    }

    public function update(Request $request, Testimony $testimony)
    {
        $this->authorize('update', $testimony->familyProfile);

        $validated = $request->validate([
            'language' => 'sometimes|string|in:es,en',
            'transcription' => 'nullable|string',
            'summary' => 'nullable|string',
            'recorded_at' => 'sometimes|date',
        ]);

        $testimony->update($validated);

        return response()->json([
            'message' => 'Testimony updated successfully',
            'data' => $testimony,
        ]);
    }

    public function destroy(Testimony $testimony)
    {
        $this->authorize('update', $testimony->familyProfile);

        // Borrar audio físico si existe
        if ($testimony->audio_path && Storage::disk('r2')->exists($testimony->audio_path)) {
            Storage::disk('r2')->delete($testimony->audio_path);
        }

        $testimony->delete();

        return response()->json(['message' => 'Testimony deleted successfully']);
    }
}
