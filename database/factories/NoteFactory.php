<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content' => fake()->paragraph(),
            'user_id' => User::factory(), 
        ];
    }
}