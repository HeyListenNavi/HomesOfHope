<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimony>
 */
class TestimonyFactory extends Factory
{
    public function definition(): array
    {
        return [
            // family_profile_id se asigna en seeder
            'language' => fake()->randomElement(['es', 'en']),
            'audio_path' => null, // Simulamos que algunos no tienen audio
            'transcription' => fake()->paragraph(3),
            'summary' => fake()->sentence(),
            'recorded_by' => User::factory(),
            'recorded_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}