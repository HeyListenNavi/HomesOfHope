<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantQuestionResponse extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'applicant_id',
        'question_id',
        'question_text_snapshot',
        'user_response',
        "ai_decision"
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}