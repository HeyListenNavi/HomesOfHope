<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $documentable = match ($validated['documentable_type']) {
            'family_profile' => FamilyProfile::findOrFail($validated['documentable_id']),
            'family_member' => FamilyMember::findOrFail($validated['documentable_id']),
            'visit' => Visit::findOrFail($validated['documentable_id']),
            default => abort(403),
        };

        $this->authorize('update', $documentable);

        $file = $request->file('file');
        $path = $file->store('documents', 'r2');

        $document = Document::create([
            'documentable_id' => $documentable->getKey(),
            'documentable_type' => $documentable->getMorphClass(),
            'document_type' => $validated['document_type'],
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'data' => $document,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        $this->authorize('view', $document->documentable);

        return $document;
    }

    /**
     * Download the file.
     */
    public function download(Document $document)
    {
        $this->authorize('view', $document->documentable);

        if (! Storage::disk('r2')->exists($document->file_path)) {
            return response()->json(['message' => 'File not found on disk'], 404);
        }

        return Storage::disk('r2')->download($document->file_path, $document->original_name);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        $this->authorize('update', $document->documentable);

        // 1. Borrar archivo físico
        if (Storage::disk('r2')->exists($document->file_path)) {
            Storage::disk('r2')->delete($document->file_path);
        }

        // 2. Borrar registro
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }
}
