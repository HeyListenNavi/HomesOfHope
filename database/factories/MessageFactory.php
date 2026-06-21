<?php

namespace Database\Factories;

use App\Enums\MessageRole;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
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
            'conversation_id' => Conversation::inRandomOrder()->first(),
            'phone' => $this->faker->phoneNumber,
            'message' => $this->faker->sentence,
            'role' => fake()->randomElement(MessageRole::cases()),
            'name' => $this->faker->name,
        ];
    }
}
