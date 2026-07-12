<?php

namespace Database\Factories;

use App\Models\Evidence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evidence>
 */
class EvidenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'file_path' => 'evidence/dummy/'.fake()->uuid().'.jpg',
            'description' => fake()->boolean(60) ? fake()->sentence() : null,
            'taken_by' => User::factory(),
        ];
    }
}
