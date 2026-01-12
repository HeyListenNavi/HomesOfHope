<?php

namespace Database\Seeders;

use App\Models\FamilyProfile;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class VisitSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos un usuario "Staff" para asignar las visitas
        $staffUser = User::first() ?? User::factory()->create();
        
        $profiles = FamilyProfile::all();

        foreach ($profiles as $profile) {
            // 1. Crear visitas históricas (completadas)
            Visit::factory()->count(rand(1, 3))->create([
                'family_profile_id' => $profile->id,
                'attended_by' => $staffUser->id,
                'status' => 'completed',
            ]);

            // 2. Crear próxima visita (programada)
            Visit::factory()->create([
                'family_profile_id' => $profile->id,
                'attended_by' => $staffUser->id,
                'status' => 'scheduled',
            ]);
        }
    }
}