<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stage>
 */
class StageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // El nombre de la etapa, como "Evaluación inicial".
            'name' => $this->faker->sentence(3),
            
            // Un número de orden único para la etapa.
            'order' => $this->faker->unique()->numberBetween(1, 1000),
            
            // El mensaje que se muestra al iniciar esta etapa.
            'starting_message' => $this->faker->sentence,
            
            // El mensaje que se muestra si la etapa es aprobada.
            'approval_message' => $this->faker->sentence,
            
            // El mensaje que se muestra si la etapa es rechazada.
            'rejection_message' => $this->faker->sentence,
            
            // Mensaje para cuando la etapa requiere una evaluación humana.
            'requires_evaluatio_message' => $this->faker->sentence,
        ];
    }
}