<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'in_progress', 'completed']);
        $dueDate = fake()->dateTimeBetween('now', '+2 weeks');

        return [
            // visit_id se asigna en seeder
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => $status,
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'due_date' => $dueDate,
            'completed_at' => $status === 'completed' ? fake()->dateTimeBetween('-1 week', 'now') : null,
            'assigned_by' => User::factory(),
            'assigned_to' => User::factory(),
        ];
    }
}