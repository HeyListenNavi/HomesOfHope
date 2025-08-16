<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Group;
use App\Models\Question;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantFactory extends Factory
{
    public function definition(): array
    {
        $isApproved = $this->faker->randomElement([null, true, false]);

        return [
            'chat_id' => $this->faker->unique()->randomElement(
                Conversation::pluck('chat_id')->toArray()
            ),
            'curp' => $this->faker->unique()->bothify('????######??????##'),
            'current_stage_id' => $isApproved == true ? Stage::latest('order')->first() : Stage::inRandomOrder()->first(),
            'current_question_id' => $isApproved == true ? Question::latest('order')->first() : Question::inRandomOrder()->first(),
            'process_status' => match ($isApproved) {
                true => 'approved',
                false => 'rejected',
                default => $this->faker->randomElement(['completed', 'in_progress'])
            },
            'is_approved' => $isApproved,
            'rejection_reason' => $isApproved == false ? $this->faker->sentence : null,
            'group_id' => $isApproved == true ? Group::inRandomOrder()->first() : null,
            'evaluation_data' => [
                'curp' => $this->faker->bothify('????######??????##'),
                'has_minor_children' => $this->faker->boolean,
                'owns_land' => $this->faker->boolean,
                'land_size_meters' => $this->faker->numberBetween(1, 100),
            ],
            'confirmation_status' => $this->faker->randomElement(['pending', 'confirmed'])
        ];
    }

    // Método para crear un solicitante aprobado
    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_approved' => true,
            'rejection_reason' => null,
            'group_id' => Group::factory(),
        ]);
    }

    // Método para crear un solicitante rechazado
    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_approved' => false,
            'rejection_reason' => $this->faker->sentence,
            'group_id' => null,
        ]);
    }
}
