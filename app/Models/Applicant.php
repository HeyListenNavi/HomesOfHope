<?php

namespace App\Models;

use App\Services\Applicant\ApplicantService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'applicant_name',
        'curp',
        'gender',
        'current_stage_id',
        'current_question_id',
        'process_status',
        'rejection_reason',
        'group_id',
        'confirmation_status',
        'reminder_level',
        'last_reminded_at',
        'current_step',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'last_reminded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (Applicant $applicant) {
            if ($applicant->chat_id) {
                $applicant->conversation()->firstOrCreate(
                    ['chat_id' => $applicant->chat_id],
                );
            }

            if ($applicant->applicant_name && is_null($applicant->current_step)) {
                app(ApplicantService::class)->startApplicantQuestions($applicant);
            }
        });
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }

    public function currentQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'current_question_id');
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class, 'chat_id', 'chat_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ApplicantQuestionResponse::class);
    }
}
