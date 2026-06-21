<?php

namespace Database\Factories;

use App\Enums\VisitLocationType;
use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(VisitStatus::cases());

        $scheduledDate = $status === VisitStatus::Completed
            ? fake()->dateTimeBetween('-1 month', 'yesterday')
            : fake()->dateTimeBetween('now', '+1 month');

        return [
            'attended_by' => User::factory(),
            'status' => $status,
            'scheduled_at' => $scheduledDate,
            'completed_at' => $status === VisitStatus::Completed ? $scheduledDate : null,
            'location_type' => fake()->randomElement(VisitLocationType::cases()),
            'outcome_summary' => $status === VisitStatus::Completed ? fake()->paragraph() : null,
        ];
    }
}
