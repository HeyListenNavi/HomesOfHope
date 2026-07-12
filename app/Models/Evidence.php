<?php

namespace App\Models;

use Database\Factories\EvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Evidence extends Model
{
    /** @use HasFactory<EvidenceFactory> */
    use HasFactory;

    // Si la tabla se creó como 'evidence' (singular), Laravel a veces necesita ayuda:
    protected $table = 'evidence';

    protected $fillable = [
        'visit_id',
        'file_path',
        'description',
        'taken_by',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
