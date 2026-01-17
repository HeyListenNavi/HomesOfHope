<?php

namespace Database\Seeders;

use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        // 1. Notas en Perfiles Familiares
        $profiles = FamilyProfile::all();
        foreach ($profiles as $profile) {
            Note::factory()->count(rand(1, 3))->create([
                'noteable_id' => $profile->id,
                'noteable_type' => FamilyProfile::class,
                'user_id' => $user->id,
            ]);
        }

        // 2. Notas en Miembros específicos
        $members = FamilyMember::all();
        foreach ($members as $member) {
            // Solo algunos miembros tendrán notas
            if (rand(0, 1)) {
                Note::factory()->create([
                    'noteable_id' => $member->id,
                    'noteable_type' => FamilyMember::class,
                    'user_id' => $user->id,
                    'content' => 'Nota médica o de comportamiento específico.'
                ]);
            }
        }
    }
}