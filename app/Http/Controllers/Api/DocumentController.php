<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'documentable_id' => 'required|integer',
            'documentable_type' => 'required|string', 
            'document_type' => 'required|string',
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        // Mapeo simple de "alias" a clases reales si quieres seguridad extra
        // O podrías usar Relation::morphMap() en AppServiceProvider
        $validTypes = [
            'family_profile' => \App\Models\FamilyProfile::class,
            'family_member' => \App\Models\FamilyMember::class,
            // Agrega más aquí conforme crezcas (visitas, tareas)
        ];

        // Normalizar el tipo si viene como alias
        $modelClass = $validTypes[$validated['documentable_type']] ?? $validated['documentable_type'];

        if (!class_exists($modelClass)) {
            return response()->json(['message' => 'Invalid documentable_type'], 400);
        }

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        // 2. Crear registro en BD
        $document = Document::create([
            'documentable_id' => $validated['documentable_id'],
            'documentable_type' => $modelClass,
            'document_type' => $validated['document_type'],
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'data' => $document
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $document = Document::findOrFail($id);
        
        // Agregamos la URL temporal o pública al response
        $data = $document->toArray();
        $data['url'] = Storage::url($document->file_path);

        return response()->json($data);
    }

    /**
     * Download the file.
     */
    public function download(string $id)
    {
        $document = Document::findOrFail($id);

        if (!Storage::exists($document->file_path)) {
            return response()->json(['message' => 'File not found on disk'], 404);
        }

        return Storage::download($document->file_path, $document->original_name);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $document = Document::findOrFail($id);

        // 1. Borrar archivo físico
        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        // 2. Borrar registro
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }
}