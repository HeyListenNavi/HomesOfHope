<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        "message",
        'capacity',
        'current_members_count',
        'date',
    ];

    // Relación con los solicitantes del grupo
    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }
}
