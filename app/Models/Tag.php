<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color'];

    public function applicants(): MorphToMany
    {
        return $this->morphedByMany(Applicant::class, 'taggable');
    }
}
