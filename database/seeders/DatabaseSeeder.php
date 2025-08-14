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
        // Crea 5 grupos
        $groups = Group::factory(5)->create();

        // Crea solicitantes aprobados y los asigna a grupos existentes
        Applicant::factory(20)
            ->state([
                'is_approved' => true,
                'rejection_reason' => null,
                'group_id' => $groups->random()->id, // Asigna un grupo aleatorio de los 5 que creaste
            ])
            ->create();

        // Crea solicitantes rechazados
        Applicant::factory(10)
            ->rejected() // Usaremos otro estado para los rechazados
            ->create();

        // El resto de tu seeder...
        Conversation::factory(15)->create();

        // Crea un usuario de ejemplo para Filament
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin')
        ]);


        // Stages & Questions
        $stage1 = Stage::create([
            'name' => 'ETAPA 1: Requisitos Básicos',
            'order' => 1,
            'approval_criteria' => ['Tiene Hijos menores de 16' => true, 'Lugar del Terreno' => 'tijuana o rosarito'],
            'rejection_message' => 'Lo siento, no cumples con los requisitos básicos para continuar con el proceso. Para calificar, es necesario tener hijos menores de 16 años viviendo contigo y un terreno en Tijuana o Rosarito con al menos un año de antigüedad.',
        ]);
        $stage2 = Stage::create([
            'name' => 'ETAPA 2: Información General',
            'order' => 2,
            'approval_criteria' => null,
            'rejection_message' => null,
        ]);
        $stage3 = Stage::create([
            'name' => 'ETAPA 3: Información Detallada de la Familia',
            'order' => 3,
            'approval_criteria' => null,
            'rejection_message' => null,
        ]);

        // Questions for Stage 1
        $stage1->questions()->createMany([
            [
                'key' => 'nombre_completo',
                'question_text' => '¿Cuál es su nombre completo?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => [],
                'order' => 1,
            ],
            [
                'key' => 'curp',
                'question_text' => '¿Cuál es su CURP?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => [],
                'order' => 2,
            ],
            [
                'key' => 'tiene_hijos_menores_16',
                'question_text' => '¿Tienes hijos menores de 16 años y que estén viviendo contigo?',
                'validation_rules' => ['requerido' => 'texto', 'otro' => 'debe ser si o no'],
                'approval_criteria' => ['respuesta_requerida' => 'si'],
                'order' => 3,
            ],
            [
                'key' => 'ciudad_terreno',
                'question_text' => '¿En qué ciudad se encuentra tu terreno?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => ['respuesta_requerida' => 'Debe de estar en Tijuana o Rosarito unicamente'],
                'order' => 4,
            ],
            [
                'key' => 'colonia_terreno',
                'question_text' => '¿En qué colonia se encuentra tu terreno?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => [],
                'order' => 5,
            ],
            [
                'key' => 'tiempo_con_terreno',
                'question_text' => '¿Cuánto tiempo tienes con este terreno?',
                'validation_rules' => ['requerido' => 'numerico', 'minimo' => '1'],
                'approval_criteria' => ['valor_requerido' => 'debe de ser mayor a 1 año o 12 meses'],
                'order' => 6,
            ],
            [
                'key' => 'fecha_primer_pago',
                'question_text' => '¿En qué mes y año dio su primera mensualidad?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => [],
                'order' => 7,
            ],
            [
                'key' => 'titular_contrato',
                'question_text' => '¿A nombre de quién o quiénes está el contrato?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => [],
                'order' => 8,
            ],
            [
                'key' => 'costo_y_enganche',
                'question_text' => '¿Cuál es el costo total del terreno y el enganche que diste?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => [],
                'order' => 9,
            ],
            [
                'key' => 'pago_mensual',
                'question_text' => '¿Cuánto pagas por el terreno cada mes?',
                'validation_rules' => ['requerido' => 'numerico', 'minimo' => '1'],
                'approval_criteria' => [],
                'order' => 10,
            ],
            [
                'key' => 'fecha_ultimo_pago',
                'question_text' => '¿Cuál fue la fecha de tu último pago del terreno?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => [],
                'order' => 11,
            ],
            [
                'key' => 'pagos_al_corriente',
                'question_text' => '¿Estás al corriente con tus pagos mensuales?',
                'validation_rules' => ['requerido' => 'texto', 'otro' => 'debe ser si o no'],
                'approval_criteria' => ['respuesta_requerida' => 'si'],
                'order' => 12,
            ],
            [
                'key' => 'vive_en_terreno',
                'question_text' => '¿Vives en tu terreno?',
                'validation_rules' => ['requerido' => 'texto', 'otro' => 'debe ser si o no'],
                'approval_criteria' => [],
                'order' => 13,
            ],
            [
                'key' => 'misma_colonia',
                'question_text' => '¿Viven en la misma colonia de donde está tu terreno?',
                'validation_rules' => ['requerido' => 'texto', 'otro' => 'debe ser si o no'],
                'approval_criteria' => [],
                'order' => 14,
            ],
        ]);

        // Questions for Stage 2
        $stage2->questions()->createMany([
            [
                'key' => 'hijos_en_escuela',
                'question_text' => '¿Tus hijos están yendo a la escuela?',
                'validation_rules' => ['requerido' => 'texto', 'otro' => 'debe ser si o no'],
                'approval_criteria' => null,
                'order' => 1,
            ],
            [
                'key' => 'grado_hijos',
                'question_text' => '¿Qué grado están cursando?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 2,
            ],
            [
                'key' => 'hijos_necesidad_especial',
                'question_text' => '¿Alguno de tus hijos tiene alguna necesidad especial?',
                'validation_rules' => ['requerido' => 'texto', 'otro' => 'debe ser si o no'],
                'approval_criteria' => null,
                'order' => 3,
            ],
            [
                'key' => 'costo_terreno',
                'question_text' => '¿Cuánto te costó tu terreno?',
                'validation_rules' => ['requerido' => 'numerico', 'minimo' => '1'],
                'approval_criteria' => null,
                'order' => 4,
            ],
            [
                'key' => 'sigue_pagando_terreno',
                'question_text' => '¿Lo sigues pagando?',
                'validation_rules' => ['requerido' => 'texto', 'otro' => 'debe ser si o no'],
                'approval_criteria' => null,
                'order' => 5,
            ],
            [
                'key' => 'cantidad_pagada_terreno',
                'question_text' => '¿Cuánto has pagado?',
                'validation_rules' => ['requerido' => 'numerico', 'minimo' => '0'],
                'approval_criteria' => null,
                'order' => 6,
            ],
        ]);

        // Questions for Stage 3
        $stage3->questions()->createMany([
            // Madre
            [
                'key' => 'nombre_madre',
                'question_text' => '¿Cuál es el nombre de la madre?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 1,
            ],
            [
                'key' => 'edad_madre',
                'question_text' => '¿Cuál es su edad?',
                'validation_rules' => ['requerido' => 'numerico', 'minimo' => '18'],
                'approval_criteria' => null,
                'order' => 2,
            ],
            [
                'key' => 'origen_madre',
                'question_text' => '¿De qué país y estado es?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 3,
            ],
            [
                'key' => 'escolaridad_madre',
                'question_text' => '¿Hasta qué grado fue a la escuela?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 4,
            ],
            [
                'key' => 'trabajo_madre',
                'question_text' => '¿En qué trabaja actualmente?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 5,
            ],
            [
                'key' => 'salario_semanal_madre',
                'question_text' => '¿Cuál es su salario semanal?',
                'validation_rules' => ['requerido' => 'numerico', 'minimo' => '0'],
                'approval_criteria' => null,
                'order' => 6,
            ],
            [
                'key' => 'madre_embarazada',
                'question_text' => '¿Está esperando un bebé?',
                'validation_rules' => ['requerido' => 'texto', 'otro' => 'debe ser si o no'],
                'approval_criteria' => null,
                'order' => 7,
            ],
            [
                'key' => 'tiempo_gestacion',
                'question_text' => 'Si la respuesta es sí: ¿Cuánto tiempo de gestación tiene?',
                'validation_rules' => ['otro' => 'texto'],
                'approval_criteria' => null,
                'order' => 8,
            ],
            [
                'key' => 'madre_deportada',
                'question_text' => '¿Usted ha sido deportada recientemente de los Estados Unidos?',
                'validation_rules' => ['requerido' => 'texto', 'otro' => 'debe ser si o no'],
                'approval_criteria' => null,
                'order' => 9,
            ],

            // Padre
            [
                'key' => 'nombre_padre',
                'question_text' => '¿Cuál es el nombre del padre?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 10,
            ],
            [
                'key' => 'edad_padre',
                'question_text' => '¿Cuál es su edad?',
                'validation_rules' => ['requerido' => 'numerico', 'minimo' => '18'],
                'approval_criteria' => null,
                'order' => 11,
            ],
            [
                'key' => 'origen_padre',
                'question_text' => '¿De qué país y estado es?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 12,
            ],
            [
                'key' => 'escolaridad_padre',
                'question_text' => '¿Hasta qué grado fue a la escuela?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 13,
            ],
            [
                'key' => 'trabajo_padre',
                'question_text' => '¿En qué trabaja actualmente?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 14,
            ],
            [
                'key' => 'salario_semanal_padre',
                'question_text' => '¿Cuál es su salario semanal?',
                'validation_rules' => ['requerido' => 'numerico', 'minimo' => '0'],
                'approval_criteria' => null,
                'order' => 15,
            ],
            [
                'key' => 'padre_visa',
                'question_text' => '¿Usted tiene visa para los Estados Unidos?',
                'validation_rules' => ['requerido' => 'texto', 'otro' => 'debe ser si o no'],
                'approval_criteria' => null,
                'order' => 16,
            ],
            [
                'key' => 'padre_deportado',
                'question_text' => '¿Usted ha sido deportado recientemente de los Estados Unidos?',
                'validation_rules' => ['requerido' => 'texto', 'otro' => 'debe ser si o no'],
                'approval_criteria' => null,
                'order' => 17,
            ],

            // Hijos
            [
                'key' => 'detalles_hijos',
                'question_text' => 'Por favor, dame el nombre y fecha de nacimiento de cada hijo/a.',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 18,
            ],
            [
                'key' => 'origen_hijo',
                'question_text' => 'Para cada hijo/a: ¿De qué país es?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 19,
            ],
            [
                'key' => 'hijos_escuela',
                'question_text' => '¿Tus hijos están yendo a la escuela? Si es así, indica el grado o grados que cursan.',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 18,
            ],
            [
                'key' => 'hijos_residencia_visa',
                'question_text' => '¿Tus hijos son residentes o tienen visa para los Estados Unidos?',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 19,
            ],

            // Otros miembros
            [
                'key' => 'otros_miembros',
                'question_text' => '¿Quiénes más vivirían en la casa de esperanza si se les llega a construir?',
                'validation_rules' => ['otro' => 'texto'],
                'approval_criteria' => null,
                'order' => 20,
            ],
            [
                'key' => 'detalles_otros_miembros',
                'question_text' => 'Dime su nombre, edad y relación con la familia.',
                'validation_rules' => ['otro' => 'texto'],
                'approval_criteria' => null,
                'order' => 21,
            ],

            // Problemas adicionales
            [
                'key' => 'adicciones_familia',
                'question_text' => '¿Existen problemas de adicción en la familia? Si es así, ¿cuáles?',
                'validation_rules' => ['otro' => 'texto'],
                'approval_criteria' => null,
                'order' => 22,
            ],
            [
                'key' => 'dialecto_familia',
                'question_text' => '¿Alguien de la familia habla algún dialecto?',
                'validation_rules' => ['otro' => 'texto'],
                'approval_criteria' => null,
                'order' => 23,
            ],

            // Coorderadas
            [
                'key' => 'coorderadas_terreno',
                'question_text' => 'Deberás enviar las coorderadas de tu terreno.',
                'validation_rules' => ['requerido' => 'texto'],
                'approval_criteria' => null,
                'order' => 24,
            ],
        ]);
    }
}
