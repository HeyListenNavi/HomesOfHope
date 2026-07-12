<?php

namespace Database\Factories;

use App\Enums\ApplicantGender;
use App\Enums\ApplicantStatus;
use App\Enums\AttendanceStatus;
use App\Models\Applicant;
use App\Models\Group;
use App\Models\Question;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantFactory extends Factory
{
    public function definition(): array
    {
        $processStatus = fake()->randomElement(ApplicantStatus::cases());

        return [
            // Se usa el chat_id de una conversación existente.
            // Asegúrate de que existan registros en la tabla 'conversations' antes de ejecutar el seeder.
            'chat_id' => $this->faker->unique()->numberBetween(1000000000, 9999999999),

            'curp' => $this->faker->unique()->bothify('????######??????##'),

            'applicant_name' => fake()->name(),

            'gender' => fake()->randomElement(ApplicantGender::cases()),

            // Los campos 'current_stage_id' y 'current_question_id' pueden ser nulos o referenciar registros existentes.
            'current_stage_id' => $this->faker->boolean(70) ? Stage::inRandomOrder()->first() : null,
            'current_question_id' => $this->faker->boolean(70) ? Question::inRandomOrder()->first() : null,

            'process_status' => $processStatus,

            'current_step' => 'ask_question',

            'rejection_reason' => $processStatus === ApplicantStatus::Rejected ? $this->faker->sentence : null,

            'group_id' => null,

            // El estado de confirmación puede ser 'pending' o 'confirmed' por defecto.
            'confirmation_status' => 'pending',
        ];
    }

    /**
     * Define un estado para crear un solicitante aprobado.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'process_status' => ApplicantStatus::Approved,
            'rejection_reason' => null,
            'group_id' => Group::inRandomOrder()->first() ?? Group::factory(),
            'confirmation_status' => 'confirmed',
        ])->afterCreating(function (Applicant $applicant) {
            $applicant->attendance()->create([
                'group_id' => $applicant->group_id,
                'attendance_code' => strtoupper(substr(md5(uniqid($applicant->id, true)), 0, 8)),
                'status' => AttendanceStatus::Pending,
            ]);
        });
    }

    /**
     * Define un estado para crear un solicitante rechazado.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'process_status' => ApplicantStatus::Rejected,
            'rejection_reason' => $this->faker->sentence,
            'group_id' => null,
            'confirmation_status' => 'canceled',
        ]);
    }

    /**
     * Define un estado para crear un solicitante en progreso.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'process_status' => ApplicantStatus::InProgress,
            'rejection_reason' => null,
            'group_id' => null,
            'confirmation_status' => 'pending',
        ]);
    }

    /**
     * Define un estado para crear un solicitante que requiere revisión.
     */
    public function requiresRevision(): static
    {
        return $this->state(fn (array $attributes) => [
            'process_status' => ApplicantStatus::RequiresRevision,
            'rejection_reason' => null,
            'group_id' => null,
            'confirmation_status' => 'pending',
        ]);
    }

    /**
     * Define un estado para crear un solicitante cancelado.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'process_status' => ApplicantStatus::Canceled,
            'rejection_reason' => null,
            'group_id' => null,
            'confirmation_status' => 'canceled',
        ]);
    }
}
