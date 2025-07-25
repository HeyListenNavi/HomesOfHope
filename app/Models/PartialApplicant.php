<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PartialApplicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'current_evaluation_status',
        'evaluation_data',
        'is_completed',
    ];

    protected $casts = [
        'evaluation_data' => 'json',
        'is_completed' => 'boolean',
    ];

    // Relación con la conversación
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    // Relación polimórfica para Conversation
    public function processable(): MorphOne
    {
        return $this->morphOne(Conversation::class, 'processable');
    }
}
