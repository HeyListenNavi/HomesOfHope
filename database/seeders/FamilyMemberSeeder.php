<?php

namespace Database\Seeders;

use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use Illuminate\Database\Seeder;

class FamilyMemberSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = FamilyProfile::all();

        foreach ($profiles as $profile) {
            // 1. Crear al responsable (Padre/Madre)
            $responsible = FamilyMember::factory()->create([
                'family_profile_id' => $profile->id,
                'relationship' => 'padre', // o madre
                'birth_date' => fake()->date('Y-m-d', '-30 years'),
                'is_responsible' => true,
            ]);

            // Actualizar el perfil con el ID de este responsable
            $profile->update(['responsible_member_id' => $responsible->id]);

            // 2. Crear miembros adicionales (Hijos, otros)
            FamilyMember::factory()->count(rand(1, 4))->create([
                'family_profile_id' => $profile->id,
                'relationship' => 'hijo',
                'is_responsible' => false,
            ]);
        }
    }
}