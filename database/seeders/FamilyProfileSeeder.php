<?php

namespace Database\Seeders;

use App\Models\FamilyProfile;
use Illuminate\Database\Seeder;

class FamilyProfileSeeder extends Seeder
{
    public function run(): void
    {
        FamilyProfile::factory()->count(20)->create();
    }
}