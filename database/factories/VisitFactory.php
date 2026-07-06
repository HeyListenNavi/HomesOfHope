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
            'status' => $status,
            'scheduled_at' => $scheduledDate,
            'completed_at' => $status === VisitStatus::Completed ? $scheduledDate : null,
            'location_type' => fake()->randomElement(VisitLocationType::cases()),
            'outcome_summary' => $status === VisitStatus::Completed ? fake()->paragraph() : null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Visit $visit) {
            $users = User::inRandomOrder()->take(rand(1, 2))->get();

            if ($users->isEmpty()) {
                $users = User::factory()->count(1)->create();
            }

            $visit->attendants()->sync($users);
        });
    }
}
