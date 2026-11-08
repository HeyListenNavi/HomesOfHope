<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evidence;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvidenceController extends Controller
{
    /**
     * Store new evidence (Upload media).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'file' => 'required|file|mimetypes:image/*,video/*,audio/*|max:51200', // Max 50MB
            'description' => 'nullable|string',
        ]);

        $this->authorize('update', Visit::findOrFail($validated['visit_id']));

        $file = $request->file('file');
        $extension = $file->clientExtension() ?: $file->extension();
        $path = $file->storeAs('evidence', Str::random(40).'.'.$extension, 'r2');

        // 2. Crear registro
        $evidence = Evidence::create([
            'visit_id' => $validated['visit_id'],
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
            'description' => $validated['description'] ?? null,
            'taken_by' => $request->user()->id,
        ]);

        return $evidence;
    }

    /**
     * Show a specific evidence.
     */
    public function show(Evidence $evidence)
    {
        $this->authorize('view', $evidence->visit);

        return $evidence;
    }

    /**
     * Delete evidence.
     */
    public function destroy(Request $request, Evidence $evidence)
    {
        $this->authorize('update', $evidence->visit);

        // 1. Borrar archivo físico
        if (Storage::disk('r2')->exists($evidence->file_path)) {
            Storage::disk('r2')->delete($evidence->file_path);
        }

        // 2. Borrar registro
        $evidence->delete();

        return response()->json(['message' => 'Evidence deleted successfully']);
    }
}
