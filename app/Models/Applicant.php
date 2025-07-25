<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'curp',
        'is_approved',
        'rejection_reason',
        'group_id',
        'final_evaluation_data',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'final_evaluation_data' => 'json',
    ];

    // Relación con el grupo asignado
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
