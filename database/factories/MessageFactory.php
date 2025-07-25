<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Conversation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
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
            'phone' => $this->faker->phoneNumber,
            'message' => $this->faker->sentence,
            'role' => $this->faker->randomElement(['user', 'assistant']),
            'name' => $this->faker->name,
        ];
    }
}
