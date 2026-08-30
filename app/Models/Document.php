<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\FilePreviewType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'documentable_id',
        'documentable_type',
        'document_type',
        'description',
        'original_name',
        'file_path',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    protected $casts = [
        'document_type' => DocumentType::class,
    ];

    /**
     * Get the parent documentable model (FamilyProfile, FamilyMember, etc.).
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who uploaded the file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Helper to get full URL if needed for API
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('r2')->url($this->file_path);
    }

    /**
     * Get the file preview classification.
     */
    public function getPreviewTypeAttribute(): FilePreviewType
    {
        return FilePreviewType::fromMimeType($this->mime_type);
    }
}
