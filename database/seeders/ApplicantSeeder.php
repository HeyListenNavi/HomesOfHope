<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Applicant;
use App\Models\Question;
use App\Models\ApplicantQuestionResponse;

class ApplicantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtén todas las preguntas una sola vez para eficiencia.
        $questions = Question::all();

        // 2. Crea solicitantes en diferentes estados.

        // 40 solicitantes mezclados (usando la definición base)
        $mixedApplicants = Applicant::factory()->count(40)->create();

        // 10 solicitantes aprobados (explícitamente para asegurar que tengan asistencia)
        $approvedApplicants = Applicant::factory()->approved()->count(10)->create();

        $allApplicants = $mixedApplicants->concat($approvedApplicants);

        $allApplicants->each(function (Applicant $applicant) use ($questions) {
            // Para cada solicitante, crea una respuesta por cada pregunta.
            foreach ($questions as $question) {
                // Crea la respuesta y asocia los IDs de solicitante y pregunta.
                ApplicantQuestionResponse::factory()->create([
                    'applicant_id' => $applicant->id,
                    'question_id' => $question->id,
                    'question_text_snapshot' => $question->question_text,
                    'user_response' => fake()->sentence(5),
                    'ai_decision' => fake()->randomElement(['valid', 'not_valid', 'requires_supervision']),
                ]);
            }
        });
    }
}
