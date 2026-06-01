<?php

namespace App\Models;

use App\Enums\ConditionLevel;
use App\Enums\Currency;
use App\Enums\HousingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class FamilyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_name',
        'slug',
        'status',
        'family_photo_path',
        'lives_on_land',
        'interviewer_id',
        'home_city',
        'home_colony',
        'home_address',
        'home_address_link',
        'land_city',
        'land_colony',
        'land_address',
        'land_address_link',
        'land_ownership_time',
        'land_total_cost',
        'land_down_payment',
        'land_monthly_payment',
        'land_currency',
        'land_last_payment_date',
        'land_is_up_to_date',
        'land_is_flat',
        'land_services',
        'home_status',
        'home_ownership_time',
        'home_owner_name',
        'home_monthly_rent',
        'home_monthly_rent_currency',
        'home_has_receipts',
        'house_description',
        'responsible_member_id',
        'opened_at',
        'closed_at',
        'has_addictions',
        'addictions_details',
        'general_observations',
    ];

    protected $casts = [
        'opened_at' => 'date',
        'closed_at' => 'date',
        'land_last_payment_date' => 'date',
        'land_is_up_to_date' => 'boolean',
        'land_is_flat' => 'boolean',
        'lives_on_land' => 'boolean',
        'has_addictions' => 'boolean',
        'land_services' => 'array',
        'home_has_receipts' => 'boolean',
        'land_currency' => Currency::class,
        'home_status' => HousingStatus::class,
        'home_monthly_rent_currency' => Currency::class,
    ];

    protected static function booted()
    {
        static::creating(function ($familyProfile) {
            if (empty($familyProfile->slug)) {
                $familyProfile->slug = uniqid('familia-'.Str::slug($familyProfile->family_name).'-');
            }
        });

        static::updating(function ($familyProfile) {
            if ($familyProfile->isDirty('family_name')) {
                $familyProfile->slug = uniqid('familia-'.Str::slug($familyProfile->family_name).'-');
            }
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                                Relationships */
    /* -------------------------------------------------------------------------- */

    /**
     * @return HasMany<FamilyMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    /**
     * @return BelongsTo<FamilyMember, $this>
     */
    public function responsibleMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'responsible_member_id');
    }

    /**
     * @return HasMany<Visit, $this>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * @return HasMany<Testimony, $this>
     */
    public function testimonies(): HasMany
    {
        return $this->hasMany(Testimony::class);
    }

    /**
     * @return MorphMany<Document, $this>
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * @return MorphMany<Note, $this>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
