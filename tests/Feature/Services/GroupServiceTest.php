<?php

namespace Tests\Feature\Services;

use App\Models\Applicant;
use App\Models\Group;
use App\Models\Message;
use App\Services\Group\GroupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GroupServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GroupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        $this->service = app(GroupService::class);
    }

    public function test_send_custom_message_to_group()
    {
        // given a group with two applicants and active sessions
        $group = Group::factory()->create();
        $applicant1 = Applicant::factory()->create(['group_id' => $group->id]);
        $applicant2 = Applicant::factory()->create(['group_id' => $group->id]);
        Message::factory()->create(['conversation_id' => $applicant1->conversation->id, 'role' => 'user']);
        Message::factory()->create(['conversation_id' => $applicant2->conversation->id, 'role' => 'user']);

        // when sending a custom message to the group
        $this->service->sendCustomMessageToGroup($group, 'Test message');

        // then messages are sent to all group members
        Http::assertSentCount(2);
    }

    public function test_resend_group_message()
    {
        // given a group with interview info and an applicant with active session
        $group = Group::factory()->create(['date_time' => now()->addDays(1), 'location' => 'Test Location', 'location_link' => 'https://maps.test', 'message' => 'Additional info']);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);

        // when resending the group interview message
        $this->service->reSendGroupMessage($group);

        // then interview details are sent correctly
        Http::assertSentCount(1);
    }

    public function test_send_interview_reminder_sends_invitation_show_url_active_session()
    {
        // given a group, applicant with active session
        $group = Group::factory()->create(['date_time' => now()->addDays(1), 'location' => 'Test Location', 'message' => 'Info message']);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);

        // when sending the reminder for 1 day remaining
        $this->service->sendInterviewReminder($applicant, 1);

        // then the message is sent via sendText and contains the invitation.show URL
        Http::assertSent(function ($request) use ($applicant, $group) {
            $url = URL::temporarySignedRoute('invitation.show', $group->date_time->addDay(), ['applicant' => $applicant]);

            return str_contains($request['text']['body'], $url)
                && str_contains($request['text']['body'], 'QR de asistencia');
        });
    }

    public function test_send_interview_reminder_sends_invitation_show_url_inactive_session()
    {
        // given a group, applicant with inactive session
        $group = Group::factory()->create(['date_time' => now()->addDays(1), 'location' => 'Test Location', 'message' => 'Info message']);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);

        // when sending the reminder for 1 day remaining
        $this->service->sendInterviewReminder($applicant, 1);

        // then the template 'detalles_extra' contains the invitation.show URL
        Http::assertSent(function ($request) use ($applicant, $group) {
            $url = URL::temporarySignedRoute('invitation.show', $group->date_time->addDay(), ['applicant' => $applicant]);

            $parameters = $request['template']['components'][0]['parameters'] ?? [];
            $detallesExtra = collect($parameters)->firstWhere('parameter_name', 'detalles_extra')['text'] ?? '';

            return $request['template']['name'] === 'recordatorio_grupo'
                && str_contains($detallesExtra, $url)
                && str_contains($detallesExtra, 'QR de asistencia');
        });
    }

    public function test_send_interview_reminder_includes_invitation_link_for_any_day()
    {
        // given a group, applicant, and 4 days remaining (inactive session)
        $group = Group::factory()->create(['date_time' => now()->addDays(4), 'location' => 'Test Location', 'message' => 'Info message']);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);

        // when sending the reminder for 4 days remaining
        $this->service->sendInterviewReminder($applicant, 4);

        // then the template 'detalles_extra' still contains the invitation.show URL
        Http::assertSent(function ($request) use ($applicant, $group) {
            $url = URL::temporarySignedRoute('invitation.show', $group->date_time->addDay(), ['applicant' => $applicant]);

            $parameters = $request['template']['components'][0]['parameters'] ?? [];
            $detallesExtra = collect($parameters)->firstWhere('parameter_name', 'detalles_extra')['text'] ?? '';

            return $request['template']['name'] === 'recordatorio_grupo'
                && str_contains($detallesExtra, $url);
        });
    }
}
