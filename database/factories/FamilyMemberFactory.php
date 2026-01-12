<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FamilyMember>
 */
class FamilyMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            // family_profile_id se pasa normalmente al crear desde el seeder
            'name' => fake()->firstName(),
            'paternal_surname' => fake()->lastName(),
            'maternal_surname' => fake()->lastName(),
            'birth_date' => fake()->date('Y-m-d', '-5 years'), // Por defecto niños, sobreescribir para adultos
            'curp' => strtoupper(fake()->bothify('????######??????##')),
            'relationship' => fake()->randomElement(['hijo', 'hija', 'sobrino']),
            'is_responsible' => false,
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'occupation' => fake()->jobTitle(),
            'medical_notes' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }
}