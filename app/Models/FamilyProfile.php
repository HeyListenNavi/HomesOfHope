<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FamilyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_name',
        'slug',
        'status',
        'family_photo_path',
        'current_address',
        'construction_address',
        'responsible_member_id',
        'opened_at',
        'closed_at',
        'general_observations',
    ];

    protected $casts = [
        'opened_at' => 'date',
        'closed_at' => 'date',
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get all members associated with this family.
     */
    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * Get the specific family member responsible for this case.
     */
    public function responsibleMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'responsible_member_id');
    }

    /**
     * Get all visits related to this family case.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * Get the testimonies given by this family.
     */
    public function testimonies(): HasMany
    {
        return $this->hasMany(Testimony::class);
    }

    /**
     * Get all documents attached to this profile (Polymorphic).
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get all notes attached to this profile (Polymorphic).
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }
}