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
use App\Models\Visit;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HomesofHopeSeeder::class,
            ConversationSeeder::class,
            MessageSeeder::class,
            QuestionSeeder::class,
            StageSeeder::class,
            GroupSeeder::class,
            ApplicantSeeder::class,
            ApplicantQuestionResponseSeeder::class,
            MessageSeeder::class,
            RolesAndPermissionsSeeder::class,
            ColonySeeder::class,
        ]);
    }
}
