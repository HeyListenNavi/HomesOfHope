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
                'key' => 'nombre_completo',
                'question_text' => '¿Cuál es su nombre completo?',
                'order' => 1,
            ],
            [
                'key' => 'curp',
                'question_text' => '¿Cuál es su CURP?',
                'validation_rules' => ['requerido' => 'texto'],
                'order' => 2,
            ],
            [
                'key' => 'tiene_hijos_menores_16',
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
                'key' => 'ciudad_terreno',
                'question_text' => '¿En qué ciudad se encuentra tu terreno?',
                'approval_criteria' => [
                    [
                        'rule' => 'approve_if',
                        'operator' => 'contains',
                        'value' => 'Tijuana'
                    ],
                    [
                        'rule' => 'approve_if',
                        'operator' => 'contains',
                        'value' => 'Rosarito'
                    ],
                    [
                        'rule' => 'reject_if',
                        'operator' => 'is_anything_else',
                    ],
                ],
                'order' => 4,
            ],
            [
                'key' => 'colonia_terreno',
                'question_text' => '¿En qué colonia se encuentra tu terreno?',
                'order' => 5,
            ],
            [
                'key' => 'tiempo_con_terreno',
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
                'key' => 'fecha_primer_pago',
                'question_text' => '¿En qué mes y año dio su primera mensualidad?',
                'order' => 7,
            ],
            [
                'key' => 'titular_contrato',
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
                'key' => 'costo_y_enganche',
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
                'key' => 'pago_mensual',
                'question_text' => '¿Cuánto pagas por el terreno cada mes?',
                'order' => 10,
            ],
            [
                'key' => 'fecha_ultimo_pago',
                'question_text' => '¿Cuál fue la fecha de tu último pago del terreno?',
                'approval_criteria' => [
                    [
                        'rule' => 'human_if',
                        'operator' => 'is_before',
                        'value' => 'hace 3 meses'
                    ],
                ],
                'order' => 11,
            ],
            [
                'key' => 'pagos_al_corriente',
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
                'key' => 'vive_en_terreno',
                'question_text' => '¿Vives en tu terreno?',
                'order' => 13,
            ],
            [
                'key' => 'misma_colonia',
                'question_text' => '¿Viven en la misma colonia de donde está tu terreno?',
                'order' => 14,
            ],
            [
                'key' => 'estado_civil_padres',
                'question_text' => '¿Cuál es el estado civil de los padres?',
                'order' => 15,
            ],
        ]);

        // Questions for Stage 2
        $stage2->questions()->createMany([
            [
                'key' => 'hijos_en_escuela',
                'question_text' => '¿Tus hijos están yendo a la escuela?',
                'order' => 1,
            ],
            [
                'key' => 'grado_hijos',
                'question_text' => '¿Qué grado están cursando?',
                'order' => 2,
            ],
            [
                'key' => 'hijos_necesidad_especial',
                'question_text' => '¿Alguno de tus hijos tiene alguna necesidad especial?',
                'order' => 3,
            ],
            [
                'key' => 'costo_terreno',
                'question_text' => '¿Cuánto te costó tu terreno?',
                'order' => 4,
            ],
            [
                'key' => 'sigue_pagando_terreno',
                'question_text' => '¿Lo sigues pagando?',
                'order' => 5,
            ],
            [
                'key' => 'cantidad_pagada_terreno',
                'question_text' => '¿Cuánto has pagado?',
                'order' => 6,
            ],
        ]);

        // Questions for Stage 3
        $stage3->questions()->createMany([
            // Madre
            [
                'key' => 'nombre_madre',
                'question_text' => '¿Cuál es el nombre de la madre?',
                'order' => 1,
            ],
            [
                'key' => 'edad_madre',
                'question_text' => '¿Cuál es su edad?',
                'order' => 2,
            ],
            [
                'key' => 'origen_madre',
                'question_text' => '¿De qué país y estado es?',
                'order' => 3,
            ],
            [
                'key' => 'escolaridad_madre',
                'question_text' => '¿Hasta qué grado fue a la escuela?',
                'order' => 4,
            ],
            [
                'key' => 'trabajo_madre',
                'question_text' => '¿En qué trabaja actualmente?',
                'order' => 5,
            ],
            [
                'key' => 'salario_semanal_madre',
                'question_text' => '¿Cuál es su salario semanal?',
                'order' => 6,
            ],
            [
                'key' => 'madre_embarazada',
                'question_text' => '¿Está esperando un bebé?',
                'order' => 7,
            ],
            [
                'key' => 'tiempo_gestacion',
                'question_text' => 'Si la respuesta es sí: ¿Cuánto tiempo de gestación tiene?',
                'order' => 8,
            ],
            [
                'key' => 'madre_deportada',
                'question_text' => '¿Usted ha sido deportada recientemente de los Estados Unidos?',
                'order' => 9,
            ],

            // Padre
            [
                'key' => 'nombre_padre',
                'question_text' => '¿Cuál es el nombre del padre?',
                'order' => 10,
            ],
            [
                'key' => 'edad_padre',
                'question_text' => '¿Cuál es su edad?',
                'order' => 11,
            ],
            [
                'key' => 'origen_padre',
                'question_text' => '¿De qué país y estado es?',
                'order' => 12,
            ],
            [
                'key' => 'escolaridad_padre',
                'question_text' => '¿Hasta qué grado fue a la escuela?',
                'order' => 13,
            ],
            [
                'key' => 'trabajo_padre',
                'question_text' => '¿En qué trabaja actualmente?',
                'order' => 14,
            ],
            [
                'key' => 'salario_semanal_padre',
                'question_text' => '¿Cuál es su salario semanal?',
                'order' => 15,
            ],
            [
                'key' => 'padre_visa',
                'question_text' => '¿Usted tiene visa para los Estados Unidos?',
                'order' => 16,
            ],
            [
                'key' => 'padre_deportado',
                'question_text' => '¿Usted ha sido deportado recientemente de los Estados Unidos?',
                'order' => 17,
            ],

            // Hijos
            [
                'key' => 'detalles_hijos',
                'question_text' => 'Por favor, dame el nombre y fecha de nacimiento de cada hijo/a.',
                'order' => 18,
            ],
            [
                'key' => 'origen_hijo',
                'question_text' => 'Para cada hijo/a: ¿De qué país es?',
                'order' => 19,
            ],
            [
                'key' => 'hijos_escuela',
                'question_text' => '¿Tus hijos están yendo a la escuela? Si es así, indica el grado o grados que cursan.',
                'order' => 18,
            ],
            [
                'key' => 'hijos_residencia_visa',
                'question_text' => '¿Tus hijos son residentes o tienen visa para los Estados Unidos?',
                'order' => 19,
            ],

            // Otros miembros
            [
                'key' => 'otros_miembros',
                'question_text' => '¿Quiénes más vivirían en la casa de esperanza si se les llega a construir?',
                'order' => 20,
            ],
            [
                'key' => 'detalles_otros_miembros',
                'question_text' => 'Dime su nombre, edad y relación con la familia.',
                'order' => 21,
            ],

            // Problemas adicionales
            [
                'key' => 'adicciones_familia',
                'question_text' => '¿Existen problemas de adicción en la familia? Si es así, ¿cuáles?',
                'order' => 22,
            ],
            [
                'key' => 'dialecto_familia',
                'question_text' => '¿Alguien de la familia habla algún dialecto?',
                'order' => 23,
            ],

            // Coorderadas
            [
                'key' => 'coorderadas_terreno',
                'question_text' => 'Deberás enviar las coorderadas de tu terreno.',
                'order' => 24,
            ],
        ]);

        // $this->call([
        //     GroupSeeder::class,
        //     StageSeeder::class,
        //     QuestionSeeder::class,
        //     ConversationSeeder::class,
        //     ApplicantSeeder::class,
        //     MessageSeeder::class,
        //     ApplicantQuestionResponseSeeder::class
        // ]);
    }
}
