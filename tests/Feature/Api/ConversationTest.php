<?php

namespace Tests\Feature\Api;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
    }

    public function test_can_get_or_create_conversation()
    {
        // given a chat id without an existing conversation

        // when getting or creating a conversation
        $response = $this->getJson('/api/bot/conversations/521000000000');

        // then a new conversation is created
        $response->assertOk();
        $this->assertDatabaseHas('conversations', ['chat_id' => '521000000000']);
    }

    public function test_returns_existing_conversation()
    {
        // given an existing conversation
        $conversation = Conversation::factory()->create(['chat_id' => '521111111111']);

        // when getting the conversation
        $response = $this->getJson('/api/bot/conversations/521111111111');

        // then the existing conversation is returned
        $response->assertOk()
            ->assertJsonFragment(['chat_id' => '521111111111']);
    }

    public function test_can_update_conversation()
    {
        // given an existing conversation
        $conversation = Conversation::factory()->create([
            'chat_id' => '521222222222',
            'current_process' => null,
        ]);

        // when updating the conversation (only specific fields are updatable)
        $response = $this->putJson('/api/bot/conversations/'.$conversation->id, [
            'current_process' => 'process_1',
        ]);

        // then the conversation returns successfully
        $response->assertOk();
        $conversation->refresh();
        $this->assertEquals('process_1', $conversation->current_process);
    }

    public function test_can_store_message()
    {
        // given an existing conversation
        $conversation = Conversation::factory()->create(['chat_id' => '521333333333']);

        // when storing a message
        $response = $this->postJson('/api/bot/messages', [
            'conversation_id' => $conversation->id,
            'phone' => '521333333333',
            'message' => 'Hello, this is a test message',
            'role' => 'user',
            'name' => 'Test User',
        ]);

        // then the message is created
        $response->assertCreated();
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'message' => 'Hello, this is a test message',
        ]);
    }

    public function test_can_get_messages()
    {
        // given a conversation with messages
        $conversation = Conversation::factory()->create(['chat_id' => '521444444444']);
        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
        ]);

        // when fetching messages
        $response = $this->getJson('/api/bot/messages/'.$conversation->id);

        // then all messages are returned
        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_store_message_validates_required_fields()
    {
        // given no conversation

        // when storing a message without required fields
        $response = $this->postJson('/api/bot/messages', []);

        // then validation fails
        $response->assertStatus(422);
    }
}
