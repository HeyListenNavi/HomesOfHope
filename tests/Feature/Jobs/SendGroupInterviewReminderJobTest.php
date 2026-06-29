<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendGroupInterviewReminderJob;
use App\Models\Applicant;
use App\Models\Group;
use App\Models\Message;
use App\Services\Group\GroupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SendGroupInterviewReminderJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_interview_reminder_for_applicant_with_group()
    {
        // given an applicant with a group and an active session
        $group = Group::factory()->create(['date_time' => now()->addDays(1), 'location' => 'Test Location', 'message' => 'Info']);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);

        // when handling the job
        $job = new SendGroupInterviewReminderJob($applicant->id, 1);
        $service = $this->mock(GroupService::class);
        $service->shouldReceive('sendInterviewReminder')->once()->with(Mockery::type(Applicant::class), 1);
        $job->handle($service);

        // then the reminder is sent
        $this->assertTrue(true);
    }

    public function test_does_nothing_if_applicant_not_found()
    {
        // given a non-existent applicant id

        // when handling the job
        $job = new SendGroupInterviewReminderJob(999, 1);
        $service = $this->mock(GroupService::class);
        $service->shouldNotReceive('sendInterviewReminder');
        $job->handle($service);

        // then no exception is thrown
        $this->assertTrue(true);
    }

    public function test_does_nothing_if_applicant_has_no_group()
    {
        // given an applicant without a group
        $applicant = Applicant::factory()->create(['group_id' => null]);

        // when handling the job
        $job = new SendGroupInterviewReminderJob($applicant->id, 1);
        $service = $this->mock(GroupService::class);
        $service->shouldNotReceive('sendInterviewReminder');
        $job->handle($service);

        // then no reminder is sent
        $this->assertTrue(true);
    }
}
