<?php

namespace Tests\Feature\Api;

use App\Models\Applicant;
use App\Models\Conversation;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BotRescheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    public function test_can_reschedule_applicant()
    {
        // given a group and an applicant assigned to it
        $group = Group::factory()->create(['name' => 'Grupo de Prueba', 'capacity' => 10]);
        $applicant = Applicant::factory()->create(['chat_id' => '526611163915', 'applicant_name' => 'Erick Junior', 'group_id' => $group->id, 'process_status' => 'approved', 'confirmation_status' => 'confirmed']);
        Conversation::create(['chat_id' => $applicant->chat_id, 'user_name' => $applicant->applicant_name]);

        // when calling the reschedule endpoint
        $response = $this->postJson("/api/bot/reschedule/{$applicant->chat_id}");

        // then the applicant is removed from the group and a new selection link is sent
        $response->assertStatus(200)->assertJson(['status' => 'success', 'message' => 'El solicitante ha sido removido del grupo y se ha enviado el nuevo enlace de selección.']);
        $applicant->refresh();
        $this->assertNull($applicant->group_id);
        $this->assertEquals('pending', $applicant->confirmation_status);
        $this->assertEquals('staff_approved', $applicant->process_status);
        Http::assertSent(fn ($request) => $request->url() == config('services.whatsapp.url') . '/messages' && $request['to'] == $applicant->chat_id);
    }

    public function test_returns_404_if_applicant_not_found()
    {
        // given a non-existent chat id
        $chatId = 'non-existent-id';

        // when calling the reschedule endpoint
        $response = $this->postJson("/api/bot/reschedule/{$chatId}");

        // then it returns a 404 error
        $response->assertStatus(404)->assertJson(['error' => 'Solicitante no encontrado.']);
    }
}
