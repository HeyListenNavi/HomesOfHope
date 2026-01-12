<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        
        // Agarramos todas las visitas
        $visits = Visit::all();

        foreach ($visits as $visit) {
            // Creamos 1 o 2 tareas por visita
            Task::factory()->count(rand(1, 2))->create([
                'visit_id' => $visit->id,
                'assigned_by' => $user->id,
                'assigned_to' => $user->id, // Autoasignada para el ejemplo
            ]);
        }
    }
}