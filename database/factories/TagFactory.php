<?php

namespace Database\Factories;

use App\Enums\TagType;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'type' => TagType::Applicant,
            'color' => fake()->hexColor(),
        ];
    }

    public function applicant(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TagType::Applicant,
        ]);
    }

    public function familyProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TagType::FamilyProfile,
        ]);
    }
}
