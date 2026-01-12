<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * List notes. Can filter by noteable_type and noteable_id.
     */
    public function index(Request $request)
    {
        $query = Note::with('author:id,name');

        // Filtros opcionales para obtener notas de un objeto específico
        if ($request->has('noteable_type') && $request->has('noteable_id')) {
            $type = $request->input('noteable_type');
            
            // Mapeo simple para facilitar el uso en frontend si envían 'family_profile' en lugar de la clase completa
            $map = [
                'family_profile' => \App\Models\FamilyProfile::class,
                'family_member' => \App\Models\FamilyMember::class,
                "note" => \App\Models\Note::class,
            ];
            
            $realType = $map[$type] ?? $type;

            $query->where('noteable_type', $realType)
                  ->where('noteable_id', $request->input('noteable_id'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'noteable_id' => 'required|integer',
            'noteable_type' => 'required|string',
            'content' => 'required|string',
            'is_private' => 'boolean',
        ]);

        // Mapeo de tipos
        $validTypes = [
            'family_profile' => \App\Models\FamilyProfile::class,
            'family_member' => \App\Models\FamilyMember::class,
            "visite" => \App\Models\Note::class,
        ];
        
        $className = $validTypes[$validated['noteable_type']] ?? $validated['noteable_type'];

        if (!class_exists($className)) {
            return response()->json(['message' => 'Invalid noteable_type'], 400);
        }

        $note = Note::create([
            'noteable_id' => $validated['noteable_id'],
            'noteable_type' => $className,
            'content' => $validated['content'],
            'is_private' => $validated['is_private'] ?? false,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Note created successfully',
            'data' => $note
        ], 201);
    }

    public function show(string $id)
    {
        $note = Note::with('author:id,name')->findOrFail($id);
        return response()->json($note);
    }

    public function update(Request $request, string $id)
    {
        $note = Note::findOrFail($id);

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
            'data' => $note
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $note = Note::findOrFail($id);

        // Opcional: Validar ownership
        if ($request->user()->id !== $note->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }
}