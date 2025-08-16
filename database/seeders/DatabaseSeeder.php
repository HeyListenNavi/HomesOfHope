<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\Applicant;
use App\Models\ApplicantQuestionResponse;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PartialApplicant;
use App\Models\Question;
use App\Models\Stage;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
        ]);

        $this->call([
            GroupSeeder::class,
            StageSeeder::class,
            QuestionSeeder::class,
            ConversationSeeder::class,
            ApplicantSeeder::class,
            MessageSeeder::class,
            ApplicantQuestionResponseSeeder::class
        ]);
    }
}
