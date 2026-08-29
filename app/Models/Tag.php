<?php

namespace App\Models;

use App\Enums\TagType;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'color'];

    protected $attributes = [
        'type' => 'applicant',
    ];

    protected function casts(): array
    {
        return [
            'type' => TagType::class,
        ];
    }

    public function applicants(): MorphToMany
    {
        return $this->morphedByMany(Applicant::class, 'taggable');
    }

    public function familyProfiles(): MorphToMany
    {
        return $this->morphedByMany(FamilyProfile::class, 'taggable');
    }

    #[Scope]
    protected function applicant(Builder $query): Builder
    {
        return $query->where('type', TagType::Applicant);
    }

    #[Scope]
    protected function familyProfile(Builder $query): Builder
    {
        return $query->where('type', TagType::FamilyProfile);
    }

    #[Scope]
    protected function ofType(Builder $query, TagType|string $type): Builder
    {
        return $query->where('type', $type);
    }
}
