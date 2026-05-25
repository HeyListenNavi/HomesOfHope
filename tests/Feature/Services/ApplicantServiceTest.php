<?php
namespace Tests\Feature\Services;
use App\Models\Applicant;
use App\Models\Conversation;
use App\Models\Question;
use App\Models\Stage;
use App\Services\Applicant\ApplicantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
}
