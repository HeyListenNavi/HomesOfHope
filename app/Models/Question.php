<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage_id',
        'key',
        'question_text',
        'validation_rules',
        'approval_criteria',
        'order',
    ];

    protected $casts = [
        'validation_rules' => 'json',
        'approval_criteria' => 'json',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function applicantResponses(): HasMany
    {
        return $this->hasMany(ApplicantQuestionResponse::class);
    }

    public function getRouteKeyName()
    {
        return 'key';
    }

    protected static function booted()
    {
        parent::boot();

        static::creating(function ($question) {
            $question->key = Str::slug($question->question_text);

            $originalSlug = $question->key;
            $count = 1;

            while (static::where('key', $question->key)->exists()) {
                $question->key = $originalSlug . '-' . $count++;
            }
        });
    }
}
