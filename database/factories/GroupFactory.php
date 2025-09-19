<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Group;

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
            // Genera un nombre de grupo único.
            'name' => 'Grupo ' . $this->faker->unique()->numberBetween(1, 10000),

            // El campo 'message' es nullable, usamos optional() para que a veces sea null.
            // Genera un párrafo de texto para simular un mensaje largo.
            'message' => $this->faker->optional()->paragraph(),
            
            // La capacidad es un número fijo para los datos de prueba.
            'capacity' => 50,
            
            // El contador de miembros será un número aleatorio entre 0 y la capacidad.
            'current_members_count' => $this->faker->numberBetween(0, 50),
            
            // El campo 'date' es nullable, usamos optional() para que a veces sea null.
            // Genera una fecha aleatoria en los próximos 2 años.
            'date_time' => $this->faker->dateTimeBetween('+0 days', '+2 years')->format('Y-m-d'),
        ];
    }
}