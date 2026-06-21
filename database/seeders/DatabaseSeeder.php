<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HomesofHopeSeeder::class,
            ConversationSeeder::class,
            GroupSeeder::class,
            ApplicantSeeder::class,
            MessageSeeder::class,
            FamilyProfileSeeder::class,
            FamilyMemberSeeder::class,
            DocumentSeeder::class,
            NoteSeeder::class,
            VisitSeeder::class,
            EvidenceSeeder::class,
            TaskSeeder::class,
            TestimonySeeder::class,
            RolesAndPermissionsSeeder::class,
            ColonySeeder::class,
        ]);
    }
}
