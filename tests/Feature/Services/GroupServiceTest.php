<?php

namespace Tests\Feature\Services;

use App\Models\Applicant;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\Message;
use App\Services\Group\GroupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        $conv1 = Conversation::factory()->create(['chat_id' => $applicant1->chat_id]);
        $conv2 = Conversation::factory()->create(['chat_id' => $applicant2->chat_id]);
        Message::factory()->create(['conversation_id' => $conv1->id, 'role' => 'user']);
        Message::factory()->create(['conversation_id' => $conv2->id, 'role' => 'user']);

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
        $conv = Conversation::factory()->create(['chat_id' => $applicant->chat_id]);
        Message::factory()->create(['conversation_id' => $conv->id, 'role' => 'user']);

        // when resending the group interview message
        $this->service->reSendGroupMessage($group);

        // then interview details are sent correctly
        Http::assertSentCount(1);
    }

    public function test_send_interview_reminder_one_day_before_adds_pdf_link_active_session()
    {
        // given a group, applicant with active session, and 1 day remaining
        $group = Group::factory()->create(['date_time' => now()->addDays(1), 'location' => 'Test Location', 'message' => 'Info message']);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);
        $conv = Conversation::factory()->create(['chat_id' => $applicant->chat_id]);
        Message::factory()->create(['conversation_id' => $conv->id, 'role' => 'user']);

        // when sending the reminder for 1 day remaining
        $this->service->sendInterviewReminder($applicant, 1);

        // then the message should be sent via sendText (due to active session) and contain the pdf invitation link
        Http::assertSent(function ($request) use ($applicant) {
            $url = route('selection.invitation.download', ['applicant' => $applicant]);

            return str_contains($request['text']['body'], $url)
                && str_contains($request['text']['body'], 'invitación en PDF');
        });
    }

    public function test_send_interview_reminder_one_day_before_adds_pdf_link_inactive_session()
    {
        // given a group, applicant with inactive session, and 1 day remaining
        $group = Group::factory()->create(['date_time' => now()->addDays(1), 'location' => 'Test Location', 'message' => 'Info message']);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);

        // when sending the reminder for 1 day remaining
        $this->service->sendInterviewReminder($applicant, 1);

        // then the message should be sent via sendTemplate and the template parameter 'detalles_extra' should contain the pdf invitation link
        Http::assertSent(function ($request) use ($applicant) {
            $url = route('selection.invitation.download', ['applicant' => $applicant]);

            // Extract the 'detalles_extra' parameter
            $parameters = $request['template']['components'][0]['parameters'] ?? [];
            $detallesExtra = collect($parameters)->firstWhere('parameter_name', 'detalles_extra')['text'] ?? '';

            return $request['template']['name'] === 'recordatorio_grupo'
                && str_contains($detallesExtra, $url)
                && str_contains($detallesExtra, 'invitación en PDF');
        });
    }

    public function test_send_interview_reminder_other_days_does_not_add_pdf_link()
    {
        // given a group, applicant, and 4 days remaining (inactive session)
        $group = Group::factory()->create(['date_time' => now()->addDays(4), 'location' => 'Test Location', 'message' => 'Info message']);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);

        // when sending the reminder for 4 days remaining
        $this->service->sendInterviewReminder($applicant, 4);

        // then the message template parameter 'detalles_extra' should NOT contain the pdf invitation link
        Http::assertSent(function ($request) use ($applicant) {
            $url = route('selection.invitation.download', ['applicant' => $applicant]);

            $parameters = $request['template']['components'][0]['parameters'] ?? [];
            $detallesExtra = collect($parameters)->firstWhere('parameter_name', 'detalles_extra')['text'] ?? '';

            return $request['template']['name'] === 'recordatorio_grupo'
                && ! str_contains($detallesExtra, $url);
        });
    }
}
