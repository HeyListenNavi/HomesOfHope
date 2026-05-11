<?php

namespace App\Models;

use App\Enums\Occupation;
use App\Enums\Relationship;
use App\Enums\MaritalStatus;
use App\Enums\EducationLevel;
use App\Enums\Religion;
use App\Enums\IndigenousLanguage;
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
        'is_land_owner',
        'phone',
        'occupation',
        'marital_status',
        'education_level',
        'education_grade',
        'weekly_income',
        'religion',
        'speaks_indigenous_language',
        'indigenous_language',
        'is_pregnant',
        'pregnancy_months',
        'medical_notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_responsible' => 'boolean',
        'is_land_owner' => 'boolean',
        'occupation' => Occupation::class,
        'relationship' => Relationship::class,
        'marital_status' => MaritalStatus::class,
        'education_level' => EducationLevel::class,
        'religion' => Religion::class,
        'speaks_indigenous_language' => 'boolean',
        'indigenous_language' => IndigenousLanguage::class,
        'is_pregnant' => 'boolean',
    ];

    protected $appends = [
        'full_name',
    ];

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

    public function getFullNameAttribute()
    {
        return $this->name.' '.$this->paternal_surname.' '.$this->maternal_surname;
    }
}
