<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}