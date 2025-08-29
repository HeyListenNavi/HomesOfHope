<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Support\Facades\DB;


class Stage extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

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