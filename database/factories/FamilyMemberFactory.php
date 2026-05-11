<?php

namespace Database\Factories;

use App\Enums\Occupation;
use App\Enums\Relationship;
use App\Enums\MaritalStatus;
use App\Enums\EducationLevel;
use App\Enums\Religion;
use App\Enums\IndigenousLanguage;
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
            'relationship' => fake()->randomElement(Relationship::cases()),
            'is_responsible' => false,
            'is_land_owner' => false,
            'phone' => fake()->phoneNumber(),
            'occupation' => fake()->randomElement(Occupation::cases()),
            'marital_status' => fake()->randomElement(MaritalStatus::cases()),
            'education_level' => fake()->randomElement(EducationLevel::cases()),
            'education_grade' => fake()->numberBetween(1, 12),
            'weekly_income' => fake()->randomFloat(2, 500, 5000),
            'religion' => fake()->randomElement(Religion::cases()),
            'indigenous_language' => fake()->boolean(20) ? fake()->randomElement(IndigenousLanguage::cases()) : null,
            'is_pregnant' => false,
            'pregnancy_months' => null,
            'medical_notes' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }
}
