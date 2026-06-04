<?php

namespace App\Console\Commands;

use App\Jobs\SendGroupInterviewReminderJob;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendGroupRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-group-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Starting SendGroupRemindersCommand...");

        $activeGroups = Group::where('date_time', '>', now())
            ->get();

        foreach ($activeGroups as $group) {
            $daysRemaining = now()->startOfDay()->diffInDays($group->date_time->startOfDay(), false);

            if (in_array($daysRemaining, [10, 4])) {
                $this->info("Sending {$daysRemaining}-day reminder for group: {$group->name}");
                Log::info("Sending {$daysRemaining}-day reminder for group: {$group->id}");

                foreach ($group->applicants as $applicant) {
                    SendGroupInterviewReminderJob::dispatch($applicant->id);
                }

                $group->update(['last_reminded_at' => now()]);
            }
        }

        Log::info("Finished SendGroupRemindersCommand.");
    }
}
