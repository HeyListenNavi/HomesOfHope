<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        "starting_message",
        'approval_message',
        'rejection_message',
        'requires_evaluatio_message'
    ];

    protected $casts = [
        'approval_criteria' => 'json',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }
}