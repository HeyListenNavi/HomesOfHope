<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\Applicant;
use App\Models\ApplicantQuestionResponse;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PartialApplicant;
use App\Models\Question;
use App\Models\Stage;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
        ]);

        User::factory()->create([
            'name' => 'Mario Borda',
            'email' => 'mario.borda@ywamsdb.org',
            'password' => bcrypt('1q2w3e4r'),
        ]);

        User::factory()->create([
            'name' => 'Montserrat Gonzales',
            'email' => 'montserrat.gonzalez@ywamsdb.org',
            'password' => bcrypt('V8l4d2s0'),
        ]);

        // Stages & Questions
        $stage1 = Stage::create([
            'name' => 'ETAPA 1: Requisitos Básicos',
            'order' => 1,
        ]);
        $stage2 = Stage::create([
            'name' => 'ETAPA 2: Información General',
            'order' => 2,
        ]);
        $stage3 = Stage::create([
            'name' => 'ETAPA 3: Información Detallada de la Familia',
            'order' => 3,
        ]);

        // Questions for Stage 1
        $stage1->questions()->createMany([
            [
                'question_text' => '¿Cuál es su nombre completo?',
                'order' => 1,
            ],
            [
                'question_text' => '¿Cuál es su CURP?',
                'order' => 2,
            ],
            [
                'question_text' => '¿Tienes hijos menores de 16 años y que estén viviendo contigo?',
                'approval_criteria' => [
                    [
                        'rule' => 'reject_if',
                        'operator' => 'is_less_than',
                        'value' => '1 hijo'
                    ],
                ],
                'order' => 3,
            ],
            [
                'question_text' => '¿En qué ciudad se encuentra tu terreno?',
                'approval_criteria' => [
                    [
                        'rule' => 'approve_if',
                        'operator' => 'contains',
                        'value' => 'Tijuana o Rosarito'
                    ],
                ],
                'order' => 4,
            ],
            [
                'question_text' => '¿En qué colonia se encuentra tu terreno?',
                'order' => 5,
            ],
            [
                'question_text' => '¿Cuánto tiempo tienes con este terreno?',
                'approval_criteria' => [
                    [
                        'rule' => 'reject_if',
                        'operator' => 'is_less_than',
                        'value' => '1 año o 12 meses'
                    ],
                ],
                'order' => 6,
            ],
            [
                'question_text' => '¿En qué mes y año dio su primera mensualidad?',
                'order' => 7,
            ],
            [
                'question_text' => '¿A nombre de quién o quiénes está el contrato?',
                'approval_criteria' => [
                    [
                        'rule' => 'approve_if',
                        'operator' => 'is',
                        'value' => 'estan casados y esta al nombre de cualquier padre'
                    ],
                    [
                        'rule' => 'approve_if',
                        'operator' => 'is',
                        'value' => 'estan en union libre y esta al nombre de la mamá'
                    ],
                    [
                        'rule' => 'approve_if',
                        'operator' => 'is',
                        'value' => 'estan en union libre y esta al nombre del padre y es padre de uno de los hijos'
                    ],
                    [
                        'rule' => 'reject_if',
                        'operator' => 'is',
                        'value' => 'estan separados y esta a nombre del esposo o esposa que abandono el hogar'
                    ],
                    [
                        'rule' => 'reject_if',
                        'operator' => 'is',
                        'value' => 'esta a nombre de una tercera persona que esta incluida en el contrato'
                    ],
                ],
                'order' => 8,
            ],
            [
                'question_text' => '¿Cuál es el costo total del terreno y el enganche que diste?',
                'approval_criteria' => [
                    [
                        'rule' => 'human_if',
                        'operator' => 'is',
                        'value' => 'pago el terrreno hace menos de 4 años'
                    ],
                ],
                'order' => 9,
            ],
            [
                'question_text' => '¿Cuánto pagas por el terreno cada mes?',
                'order' => 10,
            ],
            [
                'question_text' => '¿Cuál fue la fecha de tu último pago del terreno?',
                'approval_criteria' => [
                    [
                        'rule' => 'human_if',
                        'operator' => 'is_before',
                        'value' => 'han pasado mas de 3 meses'
                    ],
                ],
                'order' => 11,
            ],
            [
                'question_text' => '¿Estás al corriente con tus pagos mensuales?',
                'approval_criteria' => [
                    [
                        'rule' => 'reject_if',
                        'operator' => 'is',
                        'value' => 'no esta al corriente'
                    ],
                    [
                        'rule' => 'reject_if',
                        'operator' => 'is',
                        'value' => 'debe mas de 3 pagos'
                    ],
                ],
                'order' => 12,
            ],
            [
                'question_text' => '¿Vives en tu terreno?',
                'order' => 13,
            ],
            [
                'question_text' => '¿Viven en la misma colonia de donde está tu terreno?',
                'order' => 14,
            ],
            [
                'question_text' => '¿Cuál es el estado civil de los padres?',
                'order' => 15,
            ],
        ]);

        // Questions for Stage 2
        $stage2->questions()->createMany([
            [
                'question_text' => '¿Tus hijos están yendo a la escuela?',
                'order' => 1,
            ],
            [
                'question_text' => '¿Qué grado están cursando?',
                'order' => 2,
            ],
            [
                'question_text' => '¿Alguno de tus hijos tiene alguna necesidad especial?',
                'order' => 3,
            ],
            [
                'question_text' => '¿Cuánto te costó tu terreno?',
                'order' => 4,
            ],
            [
                'question_text' => '¿Lo sigues pagando?',
                'order' => 5,
            ],
            [
                'question_text' => '¿Cuánto has pagado?',
                'order' => 6,
            ],
        ]);

        // Questions for Stage 3
        $stage3->questions()->createMany([
            // Madre
            [
                'question_text' => '¿Cuál es el nombre de la madre?',
                'order' => 1,
            ],
            [
                'question_text' => '¿Cuál es su edad?',
                'order' => 2,
            ],
            [
                'question_text' => '¿De qué país y estado es?',
                'order' => 3,
            ],
            [
                'question_text' => '¿Hasta qué grado fue a la escuela?',
                'order' => 4,
            ],
            [
                'question_text' => '¿En qué trabaja actualmente?',
                'order' => 5,
            ],
            [
                'question_text' => '¿Cuál es su salario semanal?',
                'order' => 6,
            ],
            [
                'question_text' => '¿Está esperando un bebé?',
                'order' => 7,
            ],
            [
                'question_text' => 'Si la respuesta es sí: ¿Cuánto tiempo de gestación tiene?',
                'order' => 8,
            ],
            [
                'question_text' => '¿Usted ha sido deportada recientemente de los Estados Unidos?',
                'order' => 9,
            ],

            // Padre
            [
                'question_text' => '¿Cuál es el nombre del padre?',
                'order' => 10,
            ],
            [
                'question_text' => '¿Cuál es su edad?',
                'order' => 11,
            ],
            [
                'question_text' => '¿De qué país y estado es?',
                'order' => 12,
            ],
            [
                'question_text' => '¿Hasta qué grado fue a la escuela?',
                'order' => 13,
            ],
            [
                'question_text' => '¿En qué trabaja actualmente?',
                'order' => 14,
            ],
            [
                'question_text' => '¿Cuál es su salario semanal?',
                'order' => 15,
            ],
            [
                'question_text' => '¿Usted tiene visa para los Estados Unidos?',
                'order' => 16,
            ],
            [
                'question_text' => '¿Usted ha sido deportado recientemente de los Estados Unidos?',
                'order' => 17,
            ],

            // Hijos
            [
                'question_text' => 'Por favor, dame el nombre y fecha de nacimiento de cada hijo/a.',
                'order' => 18,
            ],
            [
                'question_text' => 'Para cada hijo/a: ¿De qué país es?',
                'order' => 19,
            ],
            [
                'question_text' => '¿Tus hijos están yendo a la escuela? Si es así, indica el grado o grados que cursan.',
                'order' => 18,
            ],
            [
                'question_text' => '¿Tus hijos son residentes o tienen visa para los Estados Unidos?',
                'order' => 19,
            ],

            // Otros miembros
            [
                'question_text' => '¿Quiénes más vivirían en la casa de esperanza si se les llega a construir?',
                'order' => 20,
            ],
            [
                'question_text' => 'Dime su nombre, edad y relación con la familia.',
                'order' => 21,
            ],

            // Problemas adicionales
            [
                'question_text' => '¿Existen problemas de adicción en la familia? Si es así, ¿cuáles?',
                'order' => 22,
            ],
            [
                'question_text' => '¿Alguien de la familia habla algún dialecto?',
                'order' => 23,
            ],

            // Coordenadas
            [
                'question_text' => 'Deberás enviar las coorderadas de tu terreno.',
                'order' => 24,
            ],
        ]);


        $this->call([
            ConversationSeeder::class,
            GroupSeeder::class,
            ApplicantSeeder::class,
            MessageSeeder::class,
        ]);
    }
}
