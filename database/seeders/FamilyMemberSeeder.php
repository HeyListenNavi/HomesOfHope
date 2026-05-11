<?php

namespace Database\Seeders;

use App\Enums\Relationship;
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
            $relationship = fake()->randomElement([Relationship::Father, Relationship::Mother]);
            $isPregnant = ($relationship === Relationship::Mother && fake()->boolean(20));

            $responsible = FamilyMember::factory()->create([
                'family_profile_id' => $profile->id,
                'relationship' => $relationship,
                'birth_date' => fake()->date('Y-m-d', '-30 years'),
                'is_responsible' => true,
                'is_land_owner' => true,
                'is_pregnant' => $isPregnant,
                'pregnancy_months' => $isPregnant ? fake()->numberBetween(1, 9) : null,
            ]);

            // Actualizar el perfil con el ID de este responsable
            $profile->update(['responsible_member_id' => $responsible->id]);

            // 2. Crear miembros adicionales (Hijos, abuelos, otros)
            FamilyMember::factory()->count(rand(2, 5))->create([
                'family_profile_id' => $profile->id,
                'relationship' => fn () => $rel = fake()->randomElement([
                    Relationship::Child,
                    Relationship::Child,
                    Relationship::Child, // Más probable que sean hijos
                    Relationship::Grandparent,
                    Relationship::Grandchild,
                    Relationship::Other,
                ]),
                'is_responsible' => false,
                'is_land_owner' => false,
                'is_pregnant' => false, // Opcionalmente podrías añadir lógica para hijas también si son mayores
                'pregnancy_months' => null,
            ]);
        }
    }
}
