<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Conversation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartialApplicant>
 */
class PartialApplicantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'current_evaluation_status' => $this->faker->randomElement(['solicitando_curp', 'solicitando_hijos_menores', 'solicitando_terreno']),
            'evaluation_data' => [
                'curp' => $this->faker->bothify('????######??????##'),
                'has_minor_children' => $this->faker->boolean,
            ],
            'is_completed' => false,
        ];
    }
}
