<?php

namespace App\Jobs;

use App\Models\Applicant;
use App\Services\Group\GroupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendGroupInterviewReminderJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $applicantId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(GroupService $groupService): void
    {
        $applicant = Applicant::find($this->applicantId);

        if (!$applicant || !$applicant->group) {
            return;
        }

        $groupService->sendInterviewReminder($applicant);
    }
}
