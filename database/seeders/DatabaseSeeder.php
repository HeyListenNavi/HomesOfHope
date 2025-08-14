<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\Applicant;
use App\Models\Conversation;
use App\Models\PartialApplicant;
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
        
        $stage1 = Stage::create([
            'name' => 'ETAPA 1: Requisitos Básicos',
            'order' => 1,
            'approval_criteria' => ['required_children_under_16' => true, 'land_location_in_tj_ros' => true],
            'rejection_message' => 'Lo siento, no cumples con los requisitos básicos para continuar con el proceso. Para calificar, es necesario tener hijos menores de 16 años viviendo contigo y un terreno en Tijuana o Rosarito con al menos un año de antigüedad.',
        ]);

        $stage1->questions()->createMany([
            [
                'key' => 'has_children_under_16',
                'question_text' => '¿Tienes hijos menores de 16 años y que estén viviendo contigo?',
                'validation_rules' => ['required', 'in:si,no'],
                'approval_criteria' => ['required_answer' => 'si'],
                'order' => 1,
            ],
            [
                'key' => 'land_city',
                'question_text' => '¿En qué Ciudad se encuentra tu terreno? (Solo Tijuana o Rosarito)',
                'validation_rules' => ['required', 'string'],
                'approval_criteria' => ['required_answer' => ['Tijuana', 'Rosarito']],
                'order' => 2,
            ],
            [
                'key' => 'land_ownership_time',
                'question_text' => '¿Cuánto tiempo llevas con el terreno? (Mínimo 1 año)',
                'validation_rules' => ['required', 'numeric', 'min:1'],
                'approval_criteria' => ['required_value' => '>= 1'],
                'order' => 3,
            ],
        ]);

        // Stage 2: General Information
        $stage2 = Stage::create([
            'name' => 'ETAPA 2: Información General',
            'order' => 2,
            'approval_criteria' => null,
            'rejection_message' => null,
        ]);

        $stage2->questions()->createMany([
            [
                'key' => 'marital_status',
                'question_text' => '¿Cuál es tu estado civil?',
                'validation_rules' => ['required', 'string'],
                'approval_criteria' => null,
                'order' => 1,
            ],
            [
                'key' => 'family_members',
                'question_text' => '¿Con quién vives actualmente?',
                'validation_rules' => ['required', 'array'],
                'approval_criteria' => null,
                'order' => 2,
            ],
            [
                'key' => 'special_needs_in_family',
                'question_text' => '¿Alguien en tu familia tiene alguna necesidad especial?',
                'validation_rules' => ['required', 'in:si,no'],
                'approval_criteria' => null,
                'order' => 3,
            ],
            [
                'key' => 'work_status',
                'question_text' => '¿Tienes un trabajo fijo?',
                'validation_rules' => ['required', 'in:si,no'],
                'approval_criteria' => null,
                'order' => 4,
            ],
            [
                'key' => 'weekly_salary',
                'question_text' => '¿Cuál es tu salario semanal? (Número sin símbolos)',
                'validation_rules' => ['required', 'numeric', 'min:0'],
                'approval_criteria' => null,
                'order' => 5,
            ],
        ]);

        // Stage 3: Detailed Family Information
        $stage3 = Stage::create([
            'name' => 'ETAPA 3: Información Detallada de la Familia',
            'order' => 3,
            'approval_criteria' => null,
            'rejection_message' => null,
        ]);

        $stage3->questions()->createMany([
            // Partner's Information (3B)
            [
                'key' => 'partner_name',
                'question_text' => '¿Cuál es el nombre del padre o pareja?',
                'validation_rules' => ['required', 'string'],
                'approval_criteria' => null,
                'order' => 1,
            ],
            [
                'key' => 'partner_age',
                'question_text' => '¿Cuál es su edad?',
                'validation_rules' => ['required', 'numeric', 'min:18'],
                'approval_criteria' => null,
                'order' => 2,
            ],
            [
                'key' => 'partner_origin_city_state',
                'question_text' => '¿De qué país y estado es?',
                'validation_rules' => ['required', 'string'],
                'approval_criteria' => null,
                'order' => 3,
            ],
            [
                'key' => 'partner_education_level',
                'question_text' => '¿Hasta qué grado fue a la escuela?',
                'validation_rules' => ['required', 'string'],
                'approval_criteria' => null,
                'order' => 4,
            ],
            [
                'key' => 'partner_current_job',
                'question_text' => '¿En qué trabaja actualmente?',
                'validation_rules' => ['required', 'string'],
                'approval_criteria' => null,
                'order' => 5,
            ],
            [
                'key' => 'partner_weekly_salary',
                'question_text' => '¿Cuál es su salario semanal?',
                'validation_rules' => ['required', 'numeric', 'min:0'],
                'approval_criteria' => null,
                'order' => 6,
            ],
            [
                'key' => 'partner_us_visa',
                'question_text' => '¿Tiene visa para los Estados Unidos?',
                'validation_rules' => ['required', 'in:si,no'],
                'approval_criteria' => null,
                'order' => 7,
            ],
            [
                'key' => 'partner_deported',
                'question_text' => '¿Ha sido deportado recientemente de los Estados Unidos?',
                'validation_rules' => ['required', 'in:si,no'],
                'approval_criteria' => null,
                'order' => 8,
            ],
            // Children's Information (3C)
            [
                'key' => 'children_details',
                'question_text' => 'Por favor, dame el nombre y fecha de nacimiento de cada hijo/a.',
                'validation_rules' => ['required', 'array'],
                'approval_criteria' => null,
                'order' => 9,
            ],
            [
                'key' => 'child_origin_country',
                'question_text' => 'Para cada hijo/a: ¿De qué país es?',
                'validation_rules' => ['required', 'array'],
                'approval_criteria' => null,
                'order' => 10,
            ],
            [
                'key' => 'child_school_status',
                'question_text' => '¿Va a la escuela? Si es así, ¿en qué grado está?',
                'validation_rules' => ['required', 'array'],
                'approval_criteria' => null,
                'order' => 11,
            ],
            [
                'key' => 'child_special_needs',
                'question_text' => '¿Tiene alguna necesidad especial? Si es así, ¿cuál? (Solo si aplica)',
                'validation_rules' => ['nullable', 'string'],
                'approval_criteria' => null,
                'order' => 12,
            ],
            [
                'key' => 'child_us_residency',
                'question_text' => '¿Sus hijos son residentes o tienen visa para los Estados Unidos?',
                'validation_rules' => ['required', 'array'],
                'approval_criteria' => null,
                'order' => 13,
            ],
            // Other members (3D)
            [
                'key' => 'other_members_living_in_house',
                'question_text' => '¿Quiénes más vivirían en la casa de esperanza si se les llega a construir?',
                'validation_rules' => ['nullable', 'array'],
                'approval_criteria' => null,
                'order' => 14,
            ],
            [
                'key' => 'other_members_details',
                'question_text' => 'Dime su nombre, edad y relación con la familia.',
                'validation_rules' => ['nullable', 'array'],
                'approval_criteria' => null,
                'order' => 15,
            ],
        ]);
    }
}