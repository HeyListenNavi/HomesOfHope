<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Grupo ' . $this->faker->unique()->numberBetween(1, 1000),
            'capacity' => 25,
            'current_members_count' => $this->faker->numberBetween(0, 25),
            'date' => $this->faker->dateTimeBetween('+0 days', '+2 years'),
        ];
    }
}
