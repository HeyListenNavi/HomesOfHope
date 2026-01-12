<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Testimony extends Model
{
    /** @use HasFactory<\Database\Factories\TestimonyFactory> */
    use HasFactory;

    protected $fillable = [
        'family_profile_id',
        'language',
        'audio_path',
        'transcription',
        'summary',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    /**
     * The family that gave the testimony.
     */
    public function familyProfile(): BelongsTo
    {
        return $this->belongsTo(FamilyProfile::class);
    }

    /**
     * The staff member who recorded/collected the testimony.
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getAudioUrlAttribute(): ?string
    {
        return $this->audio_path ? Storage::url($this->audio_path) : null;
    }
}