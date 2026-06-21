<?php

namespace App\Models;

use App\Enums\MessageRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'phone',
        'name',
        'message',
        'role',
    ];

    protected $casts = [
        'role' => MessageRole::class,
    ];

    // Relación con la conversación a la que pertenece el mensaje
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
