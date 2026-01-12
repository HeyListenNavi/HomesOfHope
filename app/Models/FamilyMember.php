<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_profile_id',
        'name',
        'paternal_surname',
        'maternal_surname',
        'birth_date',
        'curp',
        'relationship',
        'is_responsible',
        'phone',
        'email',
        'occupation',
        'medical_notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_responsible' => 'boolean',
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the family profile that this member belongs to.
     */
    public function familyProfile(): BelongsTo
    {
        return $this->belongsTo(FamilyProfile::class);
    }

    /**
     * Get all documents attached to this member (Polymorphic).
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get all notes attached to this member (Polymorphic).
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }
}