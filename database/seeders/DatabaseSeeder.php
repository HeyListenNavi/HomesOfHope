<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\Applicant;
use App\Models\Conversation;
use App\Models\PartialApplicant;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crea un usuario de ejemplo para Filament
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        // Crea algunos grupos
        Group::factory(5)->create();

        // Crea solicitantes aprobados y los asigna a grupos
        Applicant::factory(20)
            ->approved() // Usaremos un estado del factory para simplificar la creación
            ->create();

        // Crea solicitantes rechazados
        Applicant::factory(10)
            ->rejected() // Usaremos otro estado para los rechazados
            ->create();

        // Crea conversaciones y solicitantes parciales
        Conversation::factory(15)->create();
        PartialApplicant::factory(5)->create();
    }
}
