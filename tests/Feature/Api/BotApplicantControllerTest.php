<?php

namespace Tests\Feature\Api;

use App\Models\Applicant;
use App\Models\ApplicantQuestionResponse;
use App\Models\BotSetting;
use App\Models\Group;
use App\Models\Message;
use App\Models\Question;
use App\Models\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BotApplicantControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    public function test_start_evaluation_creates_applicant_and_response_records()
    {
        // given no existing applicant with that chat_id
        $question1 = Question::factory()->create(['stage_id' => Stage::factory()->create()->id, 'order' => 1]);
        $question2 = Question::factory()->create(['stage_id' => Stage::factory()->create()->id, 'order' => 1]);

        // when starting a new evaluation
        $response = $this->postJson('/api/bot/applicants/start', ['chat_id' => '521234567890']);

        // then the applicant is created with response records for every question
        $response->assertStatus(200)->assertJsonStructure(['applicant_id']);
        $this->assertDatabaseHas('applicants', ['chat_id' => '521234567890', 'process_status' => 'in_progress', 'current_step' => 'ask_name']);
        $this->assertDatabaseCount('applicant_question_responses', 2);
    }

    public function test_start_evaluation_returns_error_if_chat_id_exists()
    {
        // given an existing applicant with that chat_id
        Applicant::factory()->create(['chat_id' => '521234567890']);

        // when starting a new evaluation with the same chat_id
        $response = $this->postJson('/api/bot/applicants/start', ['chat_id' => '521234567890']);

        // then validation fails because chat_id must be unique
        $response->assertStatus(422);
    }

    public function test_submit_answer_for_ask_name_step()
    {
        // given an applicant at the ask_name step
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_name', 'process_status' => 'in_progress']);

        // when submitting the name
        $response = $this->postJson('/api/bot/applicants/521234567890/submit-answer', ['user_response' => 'Juan Perez', 'ai_decision' => 'valid']);

        // then the applicant name is saved and step advances to ask_curp
        $response->assertStatus(200)->assertJson(['status' => 'success']);
        $applicant->refresh();
        $this->assertEquals('Juan Perez', $applicant->applicant_name);
        $this->assertEquals('ask_curp', $applicant->current_step);
    }

    public function test_submit_answer_for_ask_curp_step()
    {
        // given an applicant at the ask_curp step
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_curp', 'process_status' => 'in_progress']);

        // when submitting the curp
        $response = $this->postJson('/api/bot/applicants/521234567890/submit-answer', ['user_response' => 'CURP123456HDF', 'ai_decision' => 'valid']);

        // then the curp is saved and step advances to ask_gender
        $response->assertStatus(200)->assertJson(['status' => 'success']);
        $applicant->refresh();
        $this->assertEquals('CURP123456HDF', $applicant->curp);
        $this->assertEquals('ask_gender', $applicant->current_step);
    }

    public function test_submit_answer_for_ask_gender_step()
    {
        // given an applicant at ask_gender step with a configured stage and question
        $stage = Stage::factory()->create(['order' => 1]);
        $question = Question::factory()->create(['stage_id' => $stage->id, 'order' => 1]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_gender', 'process_status' => 'in_progress']);

        // when submitting the gender
        $response = $this->postJson('/api/bot/applicants/521234567890/submit-answer', ['user_response' => 'woman', 'ai_decision' => 'valid']);

        // then the gender is saved and step advances to ask_question with stage and question set
        $response->assertStatus(200)->assertJson(['status' => 'success']);
        $applicant->refresh();
        $this->assertEquals('woman', $applicant->gender);
        $this->assertEquals('ask_question', $applicant->current_step);
        $this->assertEquals($stage->id, $applicant->current_stage_id);
        $this->assertEquals($question->id, $applicant->current_question_id);
    }

    public function test_submit_answer_for_ask_gender_returns_error_if_no_stage()
    {
        // given an applicant at ask_gender step with no stages configured
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_gender', 'process_status' => 'in_progress']);

        // when submitting the gender with no stages
        $response = $this->postJson('/api/bot/applicants/521234567890/submit-answer', ['user_response' => 'man', 'ai_decision' => 'valid']);

        // then a 404 error is returned
        $response->assertStatus(404)->assertJson(['error' => 'No hay etapas o preguntas configuradas.']);
    }

    public function test_submit_answer_for_ask_question_step()
    {
        // given an applicant at ask_question step with a current question
        $stage = Stage::factory()->create(['order' => 1]);
        $question = Question::factory()->create(['stage_id' => $stage->id, 'order' => 1]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_question', 'process_status' => 'in_progress', 'current_stage_id' => $stage->id, 'current_question_id' => $question->id]);

        // when submitting the answer with ai decision
        $response = $this->postJson('/api/bot/applicants/521234567890/submit-answer', ['user_response' => 'Mi respuesta', 'ai_decision' => 'valid', 'ai_explanation' => 'Cumple con los criterios']);

        // then the response is saved with the ai decision
        $response->assertStatus(200)->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('applicant_question_responses', ['applicant_id' => $applicant->id, 'question_id' => $question->id, 'user_response' => 'Mi respuesta', 'ai_decision' => 'valid', 'ai_decision_reason' => 'Cumple con los criterios']);
    }

    public function test_submit_answer_returns_404_if_applicant_not_found()
    {
        // given no applicant with that chat_id

        // when submitting an answer
        $response = $this->postJson('/api/bot/applicants/nonexistent/submit-answer', ['user_response' => 'test', 'ai_decision' => 'valid']);

        // then a 404 error is returned
        $response->assertStatus(404)->assertJson(['success' => false, 'message' => 'Aplicacion no encontrada']);
    }

    public function test_submit_answer_validates_ai_decision()
    {
        // given an applicant in progress
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_name', 'process_status' => 'in_progress']);

        // when submitting with an invalid ai_decision
        $response = $this->postJson('/api/bot/applicants/521234567890/submit-answer', ['user_response' => 'test', 'ai_decision' => 'invalid_value']);

        // then validation fails
        $response->assertStatus(422);
    }

    public function test_get_next_question_for_non_question_step()
    {
        // given an applicant at the ask_name step with a BotSetting configured
        BotSetting::create(['type' => 'question', 'name' => 'ask_name', 'value' => '¿Cuál es su nombre?']);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_name', 'process_status' => 'in_progress']);

        // when getting the next question
        $response = $this->getJson('/api/bot/applicants/521234567890/next-question');

        // then the BotSetting value is returned as the question
        $response->assertStatus(200)->assertJson(['status' => 'next_question', 'question' => ['question_text' => '¿Cuál es su nombre?', 'validation_rules' => null]]);
    }

    public function test_get_next_question_returns_404_if_no_bot_setting()
    {
        // given an applicant at ask_name step without a BotSetting
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_name', 'process_status' => 'in_progress']);

        // when getting the next question
        $response = $this->getJson('/api/bot/applicants/521234567890/next-question');

        // then a 404 error is returned
        $response->assertStatus(404)->assertJson(['error' => 'No question found']);
    }

    public function test_get_next_question_returns_next_question_in_stage()
    {
        // given an applicant at ask_question step with a stage that has multiple questions
        $stage = Stage::factory()->create(['order' => 1]);
        $question1 = Question::factory()->create(['stage_id' => $stage->id, 'order' => 1]);
        $question2 = Question::factory()->create(['stage_id' => $stage->id, 'order' => 2]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_question', 'process_status' => 'in_progress', 'current_stage_id' => $stage->id, 'current_question_id' => $question1->id]);

        // when getting the next question
        $response = $this->getJson('/api/bot/applicants/521234567890/next-question');

        // then the next question is returned and applicant advances to it
        $response->assertStatus(200)->assertJson(['status' => 'next_question', 'question' => ['id' => $question2->id]]);
        $applicant->refresh();
        $this->assertEquals($question2->id, $applicant->current_question_id);
    }

    public function test_get_next_question_returns_waiting_for_approval_at_end_of_stage()
    {
        // given an applicant at the last question of a stage
        $stage = Stage::factory()->create(['order' => 1]);
        $question1 = Question::factory()->create(['stage_id' => $stage->id, 'order' => 1]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_question', 'process_status' => 'in_progress', 'current_stage_id' => $stage->id, 'current_question_id' => $question1->id]);

        // when getting the next question (no more questions in stage)
        $response = $this->getJson('/api/bot/applicants/521234567890/next-question');

        // then waiting_for_approval status is returned
        $response->assertStatus(200)->assertJson(['status' => 'waiting_for_approval', 'stage_id' => $stage->id]);
    }

    public function test_get_next_question_returns_404_if_applicant_not_found()
    {
        // given no applicant with that chat_id

        // when getting the next question
        $response = $this->getJson('/api/bot/applicants/nonexistent/next-question');

        // then a 404 error is returned
        $response->assertStatus(404);
    }

    public function test_handle_stage_approval_approves_and_moves_to_next_stage()
    {
        // given an applicant with valid responses in stage 1, and a stage 2 exists
        $stage1 = Stage::factory()->create(['order' => 1, 'approval_message' => 'Aprobado']);
        $stage2 = Stage::factory()->create(['order' => 2]);
        $question1 = Question::factory()->create(['stage_id' => $stage1->id, 'order' => 1]);
        $question2 = Question::factory()->create(['stage_id' => $stage2->id, 'order' => 1]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'process_status' => 'in_progress', 'current_stage_id' => $stage1->id, 'current_question_id' => $question1->id]);
        ApplicantQuestionResponse::factory()->create(['applicant_id' => $applicant->id, 'question_id' => $question1->id, 'ai_decision' => 'valid']);

        // when approving the stage
        $response = $this->postJson('/api/bot/applicants/stage-approval', ['chat_id' => '521234567890']);

        // then the applicant moves to the next stage
        $response->assertStatus(200)->assertJson(['status' => 'stage_approved']);
        $applicant->refresh();
        $this->assertEquals($stage2->id, $applicant->current_stage_id);
        $this->assertEquals($question2->id, $applicant->current_question_id);
    }

    public function test_handle_stage_approval_completes_process_if_no_more_stages()
    {
        // given an applicant with valid responses in the last stage
        $stage1 = Stage::factory()->create(['order' => 1, 'approval_message' => 'Aprobado']);
        $question1 = Question::factory()->create(['stage_id' => $stage1->id, 'order' => 1]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'process_status' => 'in_progress', 'current_stage_id' => $stage1->id, 'current_question_id' => $question1->id, 'applicant_name' => 'Juan']);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);
        ApplicantQuestionResponse::factory()->create(['applicant_id' => $applicant->id, 'question_id' => $question1->id, 'ai_decision' => 'valid']);

        // when approving the stage
        $response = $this->postJson('/api/bot/applicants/stage-approval', ['chat_id' => '521234567890']);

        // then the process is completed and selection link is sent
        $response->assertStatus(200)->assertJson(['status' => 'process_completed']);
        $applicant->refresh();
        $this->assertEquals('approved', $applicant->process_status);
        $this->assertEquals('pending', $applicant->confirmation_status);
        Http::assertSent(fn ($request) => $request->url() == config('services.whatsapp.url').'/messages');
    }

    public function test_handle_stage_approval_rejects_if_not_valid_found()
    {
        // given an applicant with a not_valid response in the stage
        $stage1 = Stage::factory()->create(['order' => 1, 'rejection_message' => 'No cumples con los requisitos']);
        $question1 = Question::factory()->create(['stage_id' => $stage1->id, 'order' => 1]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'process_status' => 'in_progress', 'current_stage_id' => $stage1->id, 'current_question_id' => $question1->id]);
        ApplicantQuestionResponse::factory()->create(['applicant_id' => $applicant->id, 'question_id' => $question1->id, 'ai_decision' => 'not_valid']);

        // when approving the stage
        $response = $this->postJson('/api/bot/applicants/stage-approval', ['chat_id' => '521234567890']);

        // then the applicant is rejected
        $response->assertStatus(200)->assertJson(['status' => 'stage_rejected']);
        $applicant->refresh();
        $this->assertEquals('rejected', $applicant->process_status);
        $this->assertEquals('No cumples con los requisitos', $applicant->rejection_reason);
    }

    public function test_handle_stage_approval_requires_supervision()
    {
        // given an applicant with a requires_supervision response in the stage
        $stage1 = Stage::factory()->create(['order' => 1, 'requires_evaluatio_message' => 'Requiere revisión humana']);
        $question1 = Question::factory()->create(['stage_id' => $stage1->id, 'order' => 1]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'process_status' => 'in_progress', 'current_stage_id' => $stage1->id, 'current_question_id' => $question1->id]);
        ApplicantQuestionResponse::factory()->create(['applicant_id' => $applicant->id, 'question_id' => $question1->id, 'ai_decision' => 'requires_supervision']);

        // when approving the stage
        $response = $this->postJson('/api/bot/applicants/stage-approval', ['chat_id' => '521234567890']);

        // then the process status becomes requires_revision
        $response->assertStatus(200)->assertJson(['status' => 'requires_supervision']);
        $applicant->refresh();
        $this->assertEquals('requires_revision', $applicant->process_status);
    }

    public function test_get_stage_data_for_ai()
    {
        // given an applicant with responses in the current stage
        $stage = Stage::factory()->create(['order' => 1, 'name' => 'Evaluación inicial']);
        $question1 = Question::factory()->create(['stage_id' => $stage->id, 'order' => 1, 'approval_criteria' => ['min' => 1]]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'process_status' => 'in_progress', 'current_stage_id' => $stage->id]);
        ApplicantQuestionResponse::factory()->create(['applicant_id' => $applicant->id, 'question_id' => $question1->id, 'user_response' => 'My answer']);

        // when getting stage data for AI
        $response = $this->getJson('/api/bot/applicants/521234567890/stage-data');

        // then the stage data with questions and responses is returned
        $response->assertStatus(200)->assertJson(['stage_id' => $stage->id, 'stage_name' => 'Evaluación inicial']);
    }

    public function test_applicant_current_status_for_non_question_step()
    {
        // given an applicant at the ask_name step
        BotSetting::create(['type' => 'question', 'name' => 'ask_name', 'value' => '¿Cuál es su nombre?']);
        $stage = Stage::factory()->create(['order' => 1]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_name', 'process_status' => 'in_progress', 'current_stage_id' => $stage->id]);

        // when getting the current status
        $response = $this->getJson('/api/bot/applicants/applicant-status/521234567890');

        // then the status with the BotSetting question is returned
        $response->assertStatus(200)->assertJson(['applicant_name' => $applicant->applicant_name, 'current_stage' => $stage->id, 'status' => 'in_progress', 'current_question' => ['question_text' => '¿Cuál es su nombre?', 'question_criteria' => null]]);
    }

    public function test_applicant_current_status_for_ask_question_step()
    {
        // given an applicant at the ask_question step
        $stage = Stage::factory()->create(['order' => 1]);
        $question = Question::factory()->create(['stage_id' => $stage->id, 'order' => 1, 'approval_criteria' => ['min' => 1]]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'current_step' => 'ask_question', 'process_status' => 'in_progress', 'current_stage_id' => $stage->id, 'current_question_id' => $question->id]);

        // when getting the current status
        $response = $this->getJson('/api/bot/applicants/applicant-status/521234567890');

        // then the status with the current question details is returned
        $response->assertStatus(200)->assertJson(['applicant_name' => $applicant->applicant_name, 'current_stage' => $stage->id, 'status' => 'in_progress', 'current_question' => ['question_id' => $question->id, 'question_text' => $question->question_text, 'question_criteria' => $question->approval_criteria]]);
    }

    public function test_applicant_current_status_returns_404_if_not_found()
    {
        // given no applicant with that chat_id

        // when getting the current status
        $response = $this->getJson('/api/bot/applicants/applicant-status/nonexistent');

        // then a 404 error is returned
        $response->assertStatus(404);
    }

    public function test_current_stage_questions_returns_questions_for_stage()
    {
        // given a stage with two questions
        $stage = Stage::factory()->create();
        $question1 = Question::factory()->create(['stage_id' => $stage->id, 'order' => 1]);
        $question2 = Question::factory()->create(['stage_id' => $stage->id, 'order' => 2]);

        // when getting current stage questions
        $response = $this->getJson("/api/bot/applicants/current-stage-questions/{$stage->id}");

        // then both questions are returned
        $response->assertStatus(200)->assertJsonCount(2);
    }

    public function test_current_stage_questions_returns_404_if_stage_not_found()
    {
        // given no stage with that id

        // when getting current stage questions
        $response = $this->getJson('/api/bot/applicants/current-stage-questions/999');

        // then a 404 error is returned
        $response->assertStatus(404);
    }

    public function test_send_initial_data_updates_applicant()
    {
        // given an applicant with a conversation
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890']);

        // when sending initial data
        $response = $this->postJson('/api/bot/applicants/send-initial-data', ['chat_id' => '521234567890', 'applicant_name' => 'Juan Perez', 'curp' => 'CURP123456', 'gender' => 'man']);

        // then the applicant data and conversation name are updated
        $response->assertStatus(200);
        $applicant->refresh();
        $this->assertEquals('Juan Perez', $applicant->applicant_name);
        $this->assertEquals('CURP123456', $applicant->curp);
        $this->assertEquals('man', $applicant->gender);
        $this->assertEquals('Juan Perez', $applicant->conversation->fresh()->user_name);
    }

    public function test_send_initial_data_returns_404_if_applicant_not_found()
    {
        // given no applicant with that chat_id

        // when sending initial data
        $response = $this->postJson('/api/bot/applicants/send-initial-data', ['chat_id' => 'nonexistent', 'applicant_name' => 'Test', 'curp' => 'CURP', 'gender' => 'man']);

        // then a 404 error is returned
        $response->assertStatus(404);
    }

    public function test_update_answer_updates_response()
    {
        // given an applicant with a question response
        $stage = Stage::factory()->create();
        $question = Question::factory()->create(['stage_id' => $stage->id]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890']);
        $response = ApplicantQuestionResponse::factory()->create(['applicant_id' => $applicant->id, 'question_id' => $question->id, 'user_response' => 'Old answer']);

        // when updating the answer
        $apiResponse = $this->putJson('/api/bot/applicants/update-answer', ['chat_id' => $applicant->chat_id, 'question_id' => $question->id, 'new_response' => 'New answer']);

        // then the response is updated
        $apiResponse->assertStatus(200);
        $this->assertEquals('New answer', $response->fresh()->user_response);
    }

    public function test_update_answer_returns_404_if_applicant_not_found()
    {
        // given no applicant with that chat_id

        // when updating an answer
        $response = $this->putJson('/api/bot/applicants/update-answer', ['chat_id' => '999', 'question_id' => 1, 'new_response' => 'Test']);

        // then a 404 error is returned
        $response->assertStatus(404);
    }

    public function test_update_answer_returns_404_if_response_not_found()
    {
        // given an applicant with no response for the given question
        $stage = Stage::factory()->create();
        $question = Question::factory()->create(['stage_id' => $stage->id]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890']);

        // when updating an answer for a question without a response
        $response = $this->putJson('/api/bot/applicants/update-answer', ['chat_id' => $applicant->chat_id, 'question_id' => $question->id, 'new_response' => 'Test']);

        // then a 404 error is returned
        $response->assertStatus(404);
    }

    public function test_reschedule_removes_from_group_and_sends_link()
    {
        // given a group and an applicant assigned to it with an active session
        $group = Group::factory()->create(['capacity' => 10]);
        $applicant = Applicant::factory()->create(['chat_id' => '521234567890', 'applicant_name' => 'Nombre', 'group_id' => $group->id, 'process_status' => 'approved', 'confirmation_status' => 'confirmed']);
        Message::factory()->create(['conversation_id' => $applicant->conversation->id, 'role' => 'user']);

        // when calling the reschedule endpoint
        $response = $this->postJson("/api/bot/reschedule/{$applicant->chat_id}");

        // then the applicant is removed from the group and a new selection link is sent
        $response->assertStatus(200)->assertJson(['status' => 'success']);
        $applicant->refresh();
        $this->assertNull($applicant->group_id);
        $this->assertEquals('pending', $applicant->confirmation_status);
        $this->assertEquals('staff_approved', $applicant->process_status);
        Http::assertSent(fn ($request) => $request->url() == config('services.whatsapp.url').'/messages');
    }
}
