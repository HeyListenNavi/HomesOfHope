<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimony;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonyController extends Controller
{
    public function index(Request $request)
    {
        $query = Testimony::with(['familyProfile:id,family_name', 'recorder:id,name']);

        if ($request->has('family_profile_id')) {
            $query->where('family_profile_id', $request->family_profile_id);
        }

        return response()->json($query->latest('recorded_at')->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'family_profile_id' => 'required|exists:family_profiles,id',
            'language' => 'required|string|in:es,en',
            'audio' => 'nullable|file|mimes:mp3,wav,m4a|max:20480', // 20MB max
            'transcription' => 'nullable|string',
            'summary' => 'nullable|string',
            'recorded_at' => 'required|date',
        ]);

        $path = null;
        if ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('testimonies', 'public');
        }

        $testimony = Testimony::create([
            'family_profile_id' => $validated['family_profile_id'],
            'language' => $validated['language'],
            'audio_path' => $path,
            'transcription' => $validated['transcription'],
            'summary' => $validated['summary'],
            'recorded_at' => $validated['recorded_at'],
            'recorded_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Testimony recorded successfully',
            'data' => $testimony
        ], 201);
    }

    public function show(string $id)
    {
        $testimony = Testimony::with(['familyProfile', 'recorder'])->findOrFail($id);
        
        // Adjuntar URL del audio
        $data = $testimony->toArray();
        $data['audio_url'] = $testimony->audio_url;

        return response()->json($data);
    }

    public function update(Request $request, string $id)
    {
        $testimony = Testimony::findOrFail($id);

        $validated = $request->validate([
            'language' => 'sometimes|string|in:es,en',
            'transcription' => 'nullable|string',
            'summary' => 'nullable|string',
            'recorded_at' => 'sometimes|date',
        ]);

        $testimony->update($validated);

        return response()->json([
            'message' => 'Testimony updated successfully',
            'data' => $testimony
        ]);
    }

    public function destroy(string $id)
    {
        $testimony = Testimony::findOrFail($id);

        // Borrar audio físico si existe
        if ($testimony->audio_path && Storage::exists($testimony->audio_path)) {
            Storage::delete($testimony->audio_path);
        }

        $testimony->delete();

        return response()->json(['message' => 'Testimony deleted successfully']);
    }
}