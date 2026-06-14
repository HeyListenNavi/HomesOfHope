<?php

namespace Tests\Feature\Commands;

use App\Jobs\SendGroupInterviewReminderJob;
use App\Models\Applicant;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendGroupRemindersCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_it_dispatches_4_day_reminder()
    {
        // given a group with an interview in 4 days
        $group = Group::factory()->create([
            'date_time' => now()->addDays(4)->setHour(10)->setMinute(0),
        ]);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);

        // when running the reminders command
        $this->artisan('app:send-group-reminders')->assertExitCode(0);

        // then a reminder job is dispatched and the group last_reminded_at is updated
        Queue::assertPushed(SendGroupInterviewReminderJob::class, function ($job) use ($applicant) {
            return $job->applicantId === $applicant->id && $job->daysRemaining === 4;
        });
        $this->assertNotNull($group->fresh()->last_reminded_at);
    }

    public function test_it_dispatches_10_day_reminder()
    {
        // given a group with an interview in 10 days
        $group = Group::factory()->create([
            'date_time' => now()->addDays(10)->setHour(10)->setMinute(0),
        ]);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);

        // when running the reminders command
        $this->artisan('app:send-group-reminders')->assertExitCode(0);

        // then a reminder job is dispatched
        Queue::assertPushed(SendGroupInterviewReminderJob::class, function ($job) use ($applicant) {
            return $job->applicantId === $applicant->id && $job->daysRemaining === 10;
        });
    }

    public function test_it_dispatches_1_day_reminder()
    {
        // given a group with an interview in 1 day
        $group = Group::factory()->create([
            'date_time' => now()->addDays(1)->setHour(10)->setMinute(0),
        ]);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);

        // when running the reminders command
        $this->artisan('app:send-group-reminders')->assertExitCode(0);

        // then a reminder job is dispatched
        Queue::assertPushed(SendGroupInterviewReminderJob::class, function ($job) use ($applicant) {
            return $job->applicantId === $applicant->id && $job->daysRemaining === 1;
        });
    }

    public function test_it_does_not_dispatch_reminder_for_other_intervals()
    {
        // given a group with an interview in 5 days
        $group = Group::factory()->create([
            'date_time' => now()->addDays(5)->setHour(10)->setMinute(0),
        ]);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);

        // when running the reminders command
        $this->artisan('app:send-group-reminders')->assertExitCode(0);

        // then no reminder job is dispatched and the group last_reminded_at is not updated
        Queue::assertNotPushed(SendGroupInterviewReminderJob::class);
        $this->assertNull($group->fresh()->last_reminded_at);
    }

    public function test_it_dispatches_reminders_even_for_inactive_groups()
    {
        // given an inactive group with an interview in 10 days
        $group = Group::factory()->create([
            'date_time' => now()->addDays(10)->setHour(10)->setMinute(0),
            'is_active' => false,
        ]);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);

        // when running the reminders command
        $this->artisan('app:send-group-reminders')->assertExitCode(0);

        // then a reminder job is dispatched
        Queue::assertPushed(SendGroupInterviewReminderJob::class, function ($job) use ($applicant) {
            return $job->applicantId === $applicant->id && $job->daysRemaining === 10;
        });
    }
}
