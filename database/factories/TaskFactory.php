<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(TaskStatus::cases());
        $dueDate = fake()->dateTimeBetween('now', '+2 weeks');

        return [
            // visit_id se asigna en seeder
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => $status,
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'due_date' => $dueDate,
            'completed_at' => $status === TaskStatus::Completed ? fake()->dateTimeBetween('-1 week', 'now') : null,
            'assigned_by' => User::factory(),
            'assigned_to' => User::factory(),
        ];
    }
}
