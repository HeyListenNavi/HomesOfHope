<?php

namespace Database\Factories;

use App\Models\Colony;
use Illuminate\Database\Eloquent\Factories\Factory;

class ColonyFactory extends Factory
{
    protected $model = Colony::class;

    public function definition(): array
    {
        return [
            "city" => fake()->city,
            'name' => $this->faker->unique()->streetName(),
            'is_active' => true,
        ];
    }
}
