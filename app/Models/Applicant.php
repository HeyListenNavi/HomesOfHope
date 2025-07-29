<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Message;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'curp',
        'is_approved',
        'rejection_reason',
        'group_id',
        'final_evaluation_data',
        'conversation_id',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'final_evaluation_data' => 'json',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }


}
