<?php

namespace Database\Factories;

use App\Enums\ApprovalRule;
use App\Models\Question;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
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
            'question_text' => $this->faker->sentence,
            'approval_criteria' => [
                [
                    'rule' => fake()->randomElement(ApprovalRule::cases())->value,
                    'operator' => fake()->randomElement(['is', 'is_not', 'contains', 'is_equal_to', 'is_greater_than', 'between']),
                    'value' => fake()->word(),
                ],
            ],
            'order' => $this->faker->unique()->numberBetween(1, 1000),
        ];
    }
}
