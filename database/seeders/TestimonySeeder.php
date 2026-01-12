<?php

namespace Database\Seeders;

use App\Models\FamilyProfile;
use App\Models\Testimony;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestimonySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        $profiles = FamilyProfile::all();

        // Solo el 30% de las familias tienen testimonio
        foreach ($profiles as $profile) {
            if (rand(0, 100) < 30) {
                Testimony::factory()->create([
                    'family_profile_id' => $profile->id,
                    'recorded_by' => $user->id,
                ]);
            }
        }
    }
}