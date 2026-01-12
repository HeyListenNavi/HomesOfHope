<?php

namespace Database\Factories;

use App\Models\FamilyProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visit>
 */
class VisitFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['scheduled', 'completed', 'canceled']);
        
        $scheduledDate = $status === 'completed' 
            ? fake()->dateTimeBetween('-1 month', 'yesterday') 
            : fake()->dateTimeBetween('now', '+1 month');

        return [
            'attended_by' => User::factory(),
            'status' => $status,
            'scheduled_at' => $scheduledDate,
            'completed_at' => $status === 'completed' ? $scheduledDate : null,
            'location_type' => fake()->randomElement(['current_address', 'construction_site', 'office']),
            'outcome_summary' => $status === 'completed' ? fake()->paragraph() : null,
        ];
    }
}