<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_name',
        'current_process',
        'process_id',
        'process_status',
    ];

    // Relación con los mensajes de la conversación
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    // Relación polimórfica al proceso activo
    // No es estrictamente necesaria para esta arquitectura,
    // ya que process_id y current_process ya cumplen esa función,
    // pero es una opción más robusta para el futuro.
    public function processable(): MorphTo
    {
        return $this->morphTo('processable', 'current_process', 'process_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    // Nota de Vero real: A lo que entendi, si usas latestOfMany, te da directamente el último mensaje sin necesidad de cargar toda la conversacion
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
