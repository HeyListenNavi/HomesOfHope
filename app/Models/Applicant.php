<?php

namespace App\Models;

use App\Enums\ApplicantGender;
use App\Enums\ApplicantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    ];

    protected $casts = [
        'gender' => ApplicantGender::class,
        'process_status' => ApplicantStatus::class,
    ];

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
