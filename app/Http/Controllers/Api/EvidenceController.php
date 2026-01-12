<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evidence;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvidenceController extends Controller
{
    /**
     * Store new evidence (Upload photo).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'image' => 'required|image|max:10240', // Max 10MB, debe ser imagen
            'description' => 'nullable|string',
        ]);

        // Verificar que la visita exista (ya validado, pero podemos checar permisos aquí)
        // $visit = Visit::findOrFail($validated['visit_id']);

        // 1. Subir imagen
        $path = $request->file('image')->store('evidence', 'public');

        // 2. Crear registro
        $evidence = Evidence::create([
            'visit_id' => $validated['visit_id'],
            'file_path' => $path,
            'description' => $validated['description'],
            'taken_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Evidence uploaded successfully',
            'data' => $evidence
        ], 201);
    }

    /**
     * Show a specific evidence.
     */
    public function show(string $id)
    {
        $evidence = Evidence::findOrFail($id);
        
        return response()->json([
            'id' => $evidence->id,
            'visit_id' => $evidence->visit_id,
            'url' => $evidence->url, // Usando el accessor
            'description' => $evidence->description,
            'created_at' => $evidence->created_at,
        ]);
    }

    /**
     * Delete evidence.
     */
    public function destroy(Request $request, string $id)
    {
        $evidence = Evidence::findOrFail($id);

        // Validar permisos (opcional: solo quien la subió puede borrarla)
        // if ($evidence->taken_by !== $request->user()->id) abort(403);

        // 1. Borrar archivo físico
        if (Storage::exists($evidence->file_path)) {
            Storage::delete($evidence->file_path);
        }

        // 2. Borrar registro
        $evidence->delete();

        return response()->json(['message' => 'Evidence deleted successfully']);
    }
}