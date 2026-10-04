<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use App\Models\Note;
use App\Models\Visit;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * List notes. Can filter by noteable_type and noteable_id.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'noteable_type' => ['required', 'string'],
            'noteable_id' => ['required', 'integer'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $noteable = match ($validated['noteable_type']) {
            'family_profile' => FamilyProfile::findOrFail($validated['noteable_id']),
            'family_member' => FamilyMember::findOrFail($validated['noteable_id']),
            'visit' => Visit::findOrFail($validated['noteable_id']),
            default => abort(403),
        };

        $this->authorize('view', $noteable);

        return Note::with('author')
            ->where('noteable_type', $noteable->getMorphClass())
            ->where('noteable_id', $noteable->getKey())
            ->latest()
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'noteable_id' => 'required|integer',
            'noteable_type' => 'required|string',
            'content' => 'required|string',
            'is_private' => 'boolean',
        ]);

        $noteable = match ($validated['noteable_type']) {
            'family_profile' => FamilyProfile::findOrFail($validated['noteable_id']),
            'family_member' => FamilyMember::findOrFail($validated['noteable_id']),
            'visit' => Visit::findOrFail($validated['noteable_id']),
            default => abort(403),
        };

        $this->authorize('update', $noteable);

        $note = Note::create([
            'noteable_id' => $noteable->getKey(),
            'noteable_type' => $noteable->getMorphClass(),
            'content' => $validated['content'],
            'is_private' => $validated['is_private'] ?? false,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Note created successfully',
            'data' => $note,
        ], 201);
    }

    public function show(Note $note)
    {
        $this->authorize('view', $note->noteable);

        return $note->load('author');
    }

    public function update(Request $request, Note $note)
    {
        $this->authorize('update', $note->noteable);

        // Opcional: Validar que el usuario sea el dueño de la nota
        if ($request->user()->id !== $note->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'sometimes|string',
            'is_private' => 'boolean',
        ]);

        $note->update($validated);

        return response()->json([
            'message' => 'Note updated successfully',
            'data' => $note,
        ]);
    }

    public function destroy(Request $request, Note $note)
    {
        $this->authorize('update', $note->noteable);

        // Opcional: Validar ownership
        if ($request->user()->id !== $note->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }
}
