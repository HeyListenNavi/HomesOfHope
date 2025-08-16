<?php

namespace Database\Factories;

use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stage_id' => Stage::inRandomOrder()->first()->id,
            'key' => $this->faker->unique()->word,
            'question_text' => $this->faker->sentence,
            'validation_rules' => ['required', 'string'],
            'approval_criteria' => [
                $this->faker->word => $this->faker->word
            ],
            'order' => $this->faker->unique()->numberBetween(1, 1000),
        ];
    }
}
