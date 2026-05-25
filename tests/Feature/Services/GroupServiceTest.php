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
}
