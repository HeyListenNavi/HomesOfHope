<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantFactory extends Factory
{
    public function definition(): array
    {
        // El resto del código de la función definition() va aquí...
        $isApproved = $this->faker->boolean;

        return [
            'chat_id' => $this->faker->unique()->numerify('##########'), 
            'curp' => $this->faker->unique()->bothify('????######??????##'),
            'is_approved' => $isApproved,
            'rejection_reason' => $isApproved ? null : $this->faker->sentence,
            'group_id' => $isApproved ? Group::factory() : null,
            'evaluation_data' => [
                'curp' => $this->faker->bothify('????######??????##'),
                'has_minor_children' => $this->faker->boolean,
                'owns_land' => $this->faker->boolean,
                'land_size_meters' => $this->faker->numberBetween(1, 100),
            ],
        ];
    }

    // Método para crear un solicitante aprobado
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
            'rejection_reason' => null,
            'group_id' => Group::factory(),
        ]);
    }

    // Método para crear un solicitante rechazado
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
            'rejection_reason' => $this->faker->sentence,
            'group_id' => null,
        ]);
    }
}
