<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evidence>
 */
class EvidenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'file_path' => 'evidence/dummy/' . fake()->uuid() . '.jpg',
            'taken_by' => User::factory(),
        ];
    }
}