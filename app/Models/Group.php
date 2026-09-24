<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $appends = ['current_members_count'];

    protected $fillable = [
        'name',
        'message',
        'capacity',
        'date_time',
        'location',
        'location_link',
        'is_active',
        'last_reminded_at',
        'attendance_closed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_time' => 'datetime',
            'is_active' => 'boolean',
            'last_reminded_at' => 'datetime',
            'attendance_closed_at' => 'datetime',
        ];
    }

    public function getCurrentMembersCountAttribute(): int
    {
        return $this->applicants()->count();
    }

    // Relación con los solicitantes del grupo
    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    #[Scope]
    protected function enrollable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNull('attendance_closed_at')
            ->whereNotNull('date_time')
            ->where('date_time', '>=', now())
            ->whereRaw('(select count(*) from applicants where applicants.group_id = groups.id) < capacity');
    }
}
