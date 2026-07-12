<?php

namespace Database\Seeders;

use App\Models\FamilyProfile;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class VisitSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = FamilyProfile::all();

        foreach ($profiles as $profile) {
            Visit::factory()->count(rand(1, 3))->create([
                'family_profile_id' => $profile->id,
            ]);
        }
    }
}
