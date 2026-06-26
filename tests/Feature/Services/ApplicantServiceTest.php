<?php

namespace Tests\Feature\Services;

use App\Models\Applicant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Question;
use App\Models\Stage;
use App\Services\Applicant\ApplicantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ApplicantServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ApplicantService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        $this->service = app(ApplicantService::class);
    }

    public function test_reset_applicant_restarts_the_process()
    {
        // given a staged applicant with a conversation
        $stage1 = Stage::factory()->create(['order' => 1]);
        $stage2 = Stage::factory()->create(['order' => 2]);
        $question1 = Question::factory()->create(['stage_id' => $stage1->id, 'order' => 1]);
        $applicant = Applicant::factory()->create(['current_stage_id' => $stage2->id, 'process_status' => 'staff_approved']);
        Conversation::factory()->create(['chat_id' => $applicant->chat_id]);

        // when resetting the applicant
        $this->service->resetApplicant($applicant);

        // then status is in_progress and stage is reset
        $applicant->refresh();
        $this->assertEquals($stage1->id, $applicant->current_stage_id);
        $this->assertEquals($question1->id, $applicant->current_question_id);
        $this->assertEquals('in_progress', $applicant->process_status);
        $this->assertNull($applicant->group_id);
    }

    public function test_approve_stage_moves_applicant_to_next_stage()
    {
        // given an applicant in stage 1
        $stage1 = Stage::factory()->create(['order' => 1]);
        $stage2 = Stage::factory()->create(['order' => 2]);
        $question2 = Question::factory()->create(['stage_id' => $stage2->id, 'order' => 1]);
        $applicant = Applicant::factory()->create(['current_stage_id' => $stage1->id, 'process_status' => 'in_progress']);
        Conversation::factory()->create(['chat_id' => $applicant->chat_id]);

        // when approving the stage
        $this->service->approveStage($applicant);

        // then applicant moves to stage 2
        $applicant->refresh();
        $this->assertEquals($stage2->id, $applicant->current_stage_id);
        $this->assertEquals($question2->id, $applicant->current_question_id);
    }

    public function test_approve_stage_completes_process_if_no_more_stages()
    {
        // given an applicant in the last stage
        $stage1 = Stage::factory()->create(['order' => 1]);
        $applicant = Applicant::factory()->create(['current_stage_id' => $stage1->id, 'process_status' => 'in_progress']);
        Conversation::factory()->create(['chat_id' => $applicant->chat_id]);

        // when approving the stage
        $this->service->approveStage($applicant);

        // then status becomes staff_approved
        $applicant->refresh();
        $this->assertEquals('staff_approved', $applicant->process_status);
    }

    public function test_reject_applicant_updates_status_and_reason()
    {
        // given an applicant in progress
        $applicant = Applicant::factory()->create(['process_status' => 'in_progress']);
        Conversation::factory()->create(['chat_id' => $applicant->chat_id]);

        // when rejecting the applicant
        $this->service->rejectApplicant($applicant, 'no_children');

        // then status is staff_rejected and reason is set
        $applicant->refresh();
        $this->assertEquals('staff_rejected', $applicant->process_status);
        $this->assertEquals('no_children', $applicant->rejection_reason);
    }

    public function test_re_send_current_question_when_not_in_process()
    {
        // given an applicant with status staff_approved and a current question
        $stage1 = Stage::factory()->create(['order' => 1]);
        $question1 = Question::factory()->create(['stage_id' => $stage1->id, 'order' => 1]);
        $applicant = Applicant::factory()->create(['current_stage_id' => $stage1->id, 'current_question_id' => $question1->id, 'process_status' => 'staff_approved']);
        Conversation::factory()->create(['chat_id' => $applicant->chat_id]);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);

        // when resending the current question
        $this->service->reSendCurrentQuestion($applicant);

        // then status is corrected to in_progress and a message is sent
        $applicant->refresh();
        $this->assertEquals('in_progress', $applicant->process_status);
        Http::assertSent(fn ($request) => $request->url() == config('services.whatsapp.url') . '/messages');
    }

    public function test_re_send_current_question_does_nothing_if_no_question()
    {
        // given an applicant without a current question
        $applicant = Applicant::factory()->create(['current_question_id' => null, 'process_status' => 'in_progress']);

        // when resending the current question
        $this->service->reSendCurrentQuestion($applicant);

        // then no message is sent
        Http::assertNothingSent();
    }

    public function test_send_selection_link_sends_whatsapp_message_with_signed_url()
    {
        // given an approved applicant with a conversation and active session
        $stage1 = Stage::factory()->create(['order' => 1]);
        $applicant = Applicant::factory()->create(['applicant_name' => 'Juan', 'process_status' => 'approved']);
        Conversation::factory()->create(['chat_id' => $applicant->chat_id]);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);

        // when sending the selection link
        $this->service->sendSelectionLink($applicant);

        // then a WhatsApp message is sent containing the signed group selection URL
        Http::assertSent(function ($request) use ($applicant) {
            $expectedUrl = URL::temporarySignedRoute('group.selection.form', now()->addDays(3), ['applicant' => $applicant->id]);

            return str_contains($request['text']['body'], $expectedUrl);
        });
    }

    public function test_re_send_group_selection_link_resets_and_sends_link()
    {
        // given an applicant with a group assigned and confirmed
        $stage1 = Stage::factory()->create(['order' => 1]);
        $group = \App\Models\Group::factory()->create();
        $applicant = Applicant::factory()->create(['process_status' => 'approved', 'group_id' => $group->id, 'confirmation_status' => 'confirmed', 'applicant_name' => 'Maria']);
        Conversation::factory()->create(['chat_id' => $applicant->chat_id]);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);

        // when resending the group selection link
        $this->service->reSendGroupSelectionLink($applicant);

        // then status is staff_approved, group removed, and WhatsApp message sent
        $applicant->refresh();
        $this->assertEquals('staff_approved', $applicant->process_status);
        $this->assertNull($applicant->group_id);
        $this->assertEquals('pending', $applicant->confirmation_status);
        Http::assertSent(fn ($request) => $request->url() == config('services.whatsapp.url') . '/messages');
    }

    public function test_approve_applicant_final_sets_staff_approved_and_sends_link()
    {
        // given an applicant in progress
        $stage1 = Stage::factory()->create(['order' => 1]);
        $group = \App\Models\Group::factory()->create();
        $applicant = Applicant::factory()->create(['process_status' => 'in_progress', 'group_id' => $group->id]);
        Conversation::factory()->create(['chat_id' => $applicant->chat_id]);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);

        // when approving the applicant final
        $this->service->approveApplicantFinal($applicant);

        // then status is staff_approved, group removed, and selection link sent
        $applicant->refresh();
        $this->assertEquals('staff_approved', $applicant->process_status);
        $this->assertNull($applicant->group_id);
        Http::assertSent(fn ($request) => $request->url() == config('services.whatsapp.url') . '/messages');
    }

    public function test_send_custom_message_sends_text_to_applicant()
    {
        // given an applicant with an active conversation session
        $applicant = Applicant::factory()->create();
        Conversation::factory()->create(['chat_id' => $applicant->chat_id]);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);

        // when sending a custom message
        $this->service->sendCustomMessage($applicant, 'Hello test');

        // then the message is sent via WhatsApp
        Http::assertSent(function ($request) {
            return $request['text']['body'] === 'Hello test';
        });
    }

    public function test_reset_applicant_handles_no_stages_gracefully()
    {
        // given an applicant with no stages in the database
        $applicant = Applicant::factory()->create(['process_status' => 'in_progress']);

        // when resetting the applicant
        $this->service->resetApplicant($applicant);

        // then an error message is sent via WhatsApp
        $applicant->refresh();
        Http::assertSent(fn ($request) => ($request['template']['name'] ?? '') === 'error_reinicio_etapa');
    }

    public function test_approve_stage_does_nothing_if_no_current_stage()
    {
        // given an applicant with no current stage
        $applicant = Applicant::factory()->create(['current_stage_id' => null, 'process_status' => 'in_progress']);

        // when approving the stage
        $this->service->approveStage($applicant);

        // then nothing changes
        $applicant->refresh();
        $this->assertEquals('in_progress', $applicant->process_status);
    }

    public function test_reject_applicant_sends_known_rejection_reason()
    {
        // given an applicant in progress
        $applicant = Applicant::factory()->create(['process_status' => 'in_progress']);
        Conversation::factory()->create(['chat_id' => $applicant->chat_id]);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);

        // when rejecting with a known reason key
        $this->service->rejectApplicant($applicant, 'no_children');

        // then the rejection message is expanded and sent
        $applicant->refresh();
        $this->assertEquals('staff_rejected', $applicant->process_status);
        Http::assertSent(function ($request) {
            return str_contains($request['text']['body'], 'hijos menores');
        });
    }

    public function test_reject_applicant_sends_custom_reason_as_is()
    {
        // given an applicant in progress
        $applicant = Applicant::factory()->create(['process_status' => 'in_progress']);
        Conversation::factory()->create(['chat_id' => $applicant->chat_id]);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);

        // when rejecting with a custom reason not in the predefined map
        $this->service->rejectApplicant($applicant, 'custom_reason');

        // then the reason is used as-is and sent
        $applicant->refresh();
        $this->assertEquals('custom_reason', $applicant->rejection_reason);
        Http::assertSent(function ($request) {
            return str_contains($request['text']['body'], 'custom_reason');
        });
    }
}
