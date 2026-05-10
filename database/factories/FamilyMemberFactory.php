<?php

namespace Database\Factories;

use App\Enums\Occupation;
use App\Models\FamilyMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamilyMember>
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
            'occupation' => fake()->randomElement(Occupation::cases()),
            'medical_notes' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }
}
