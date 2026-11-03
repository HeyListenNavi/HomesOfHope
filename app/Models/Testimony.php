<?php

namespace App\Models;

use App\Jobs\ProcessTestimonyTranscriptionJob;
use Database\Factories\TestimonyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Testimony extends Model
{
    /** @use HasFactory<TestimonyFactory> */
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

    protected $appends = ['audio_url'];

    protected static function booted(): void
    {
        static::created(function (Testimony $testimony): void {
            if (filled($testimony->audio_path)) {
                ProcessTestimonyTranscriptionJob::dispatch($testimony);
            }
        });

        static::updated(function (Testimony $testimony): void {
            if ($testimony->wasChanged('audio_path') && filled($testimony->audio_path)) {
                ProcessTestimonyTranscriptionJob::dispatch($testimony);
            }
        });
    }

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
        return $this->audio_path ? Storage::disk('r2')->url($this->audio_path) : null;
    }
}
