<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_id' => $this->faker->unique()->numerify('##########'),
            'current_process' => $this->faker->randomElement(['applicant_process', 'review_process']),
            'process_status' => $this->faker->randomElement(['in_progress', 'completed', 'pending', 'rejected']),
            'user_name' => $this->faker->name,
        ];
    }
}
