<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HomesofHopeSeeder::class,
            RolesAndPermissionsSeeder::class,
            BotSettingSeeder::class,
            ColonySeeder::class,
            StageSeeder::class,
            QuestionSeeder::class,
            GroupSeeder::class,
            ApplicantSeeder::class,
            ApplicantQuestionResponseSeeder::class,
            ConversationSeeder::class,
            MessageSeeder::class,
            FamilyProfileSeeder::class,
            FamilyMemberSeeder::class,
            VisitSeeder::class,
            TaskSeeder::class,
            EvidenceSeeder::class,
            DocumentSeeder::class,
            NoteSeeder::class,
            TestimonySeeder::class,
        ]);
    }
}
