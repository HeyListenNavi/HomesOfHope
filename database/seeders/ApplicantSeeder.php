<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\ApplicantQuestionResponse;
use App\Models\Question;
use Illuminate\Database\Seeder;

class ApplicantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtén todas las preguntas una sola vez para eficiencia.
        $questions = Question::all();

        // 2. Crea 50 solicitantes.
        Applicant::factory()
            ->count(50)
            ->create()
            ->each(function (Applicant $applicant) use ($questions) {
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
