<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApplicantQuestionResponse>
 */
class ApplicantQuestionResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $question = Question::inRandomOrder()->first();

        return [
            'applicant_id' => Applicant::inRandomOrder()->first()?->id,
            'question_id' => $question->id,
            'question_text_snapshot' => $question->question_text,
            'user_response' => $this->faker->word,
            'ai_decision' => fake()->randomElement(['valid', 'not_valid', 'requires_supervision']),
        ];
    }
}
