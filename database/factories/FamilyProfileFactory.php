<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FamilyProfileFactory extends Factory
{
    public function definition(): array
    {
        $familyName = 'Familia ' . fake()->lastName() . ' ' . fake()->lastName();

        return [
            'family_name' => $familyName,
            'slug' => Str::slug($familyName) . '-' . Str::random(5),
            'status' => fake()->randomElement(['prospect', 'active', 'in_follow_up', 'closed']),
            'family_photo_path' => null, // O usar fake()->imageUrl() si deseas
            'current_address' => [
                'street' => fake()->streetName(),
                'number' => fake()->buildingNumber(),
                'city' => fake()->city(),
                'zip' => fake()->postcode(),
            ],
            'construction_address' => fake()->streetName() . ' Lote ' . fake()->randomDigit() . 'city' . fake()->city(),
            'opened_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'closed_at' => fake()->boolean(20) ? fake()->dateTimeBetween('now', '+1 year') : null,
            'general_observations' => fake()->paragraph(),
        ];
    }
}