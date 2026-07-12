<?php

namespace Tests\Feature\Api;

use App\Models\FamilyProfile;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    private FamilyProfile $familyProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
        $this->familyProfile = FamilyProfile::factory()->create();
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    public function test_unauthenticated_user_cannot_access_notes()
    {
        // given no authentication

        // when fetching notes
        $response = $this->getJson('/api/notes');

        // then access is denied
        $response->assertUnauthorized();
    }

    public function test_can_list_notes()
    {
        // given existing notes
        Note::factory()->count(2)->create([
            'noteable_id' => $this->familyProfile->id,
            'noteable_type' => FamilyProfile::class,
            'user_id' => $this->user->id,
        ]);

        // when fetching the list
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/notes');

        // then all notes are returned
        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_list_notes_filtered_by_notable()
    {
        // given notes on different models
        Note::factory()->create([
            'noteable_id' => $this->familyProfile->id,
            'noteable_type' => FamilyProfile::class,
            'user_id' => $this->user->id,
            'content' => 'Note on profile',
        ]);

        // when filtering by noteable
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/notes?noteable_type=family_profile&noteable_id='.$this->familyProfile->id);

        // then only matching notes are returned
        $response->assertOk();
        $this->assertStringContainsString('Note on profile', $response->getContent());
    }

    public function test_can_create_note()
    {
        // given note data

        // when creating a note
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/notes', [
                'noteable_id' => $this->familyProfile->id,
                'noteable_type' => 'family_profile',
                'content' => 'Test note content',
            ]);

        // then the note is created
        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Note created successfully']);
        $this->assertDatabaseHas('notes', ['content' => 'Test note content']);
    }

    public function test_creates_note_with_authenticated_user()
    {
        // given note data

        // when creating a note
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/notes', [
                'noteable_id' => $this->familyProfile->id,
                'noteable_type' => 'family_profile',
                'content' => 'Note by authenticated user',
            ]);

        // then the note is associated with the current user
        $response->assertCreated();
        $this->assertDatabaseHas('notes', [
            'content' => 'Note by authenticated user',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_show_note()
    {
        // given an existing note
        $note = Note::factory()->create([
            'noteable_id' => $this->familyProfile->id,
            'noteable_type' => FamilyProfile::class,
            'user_id' => $this->user->id,
            'content' => 'Display this note',
        ]);

        // when fetching the note
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/notes/'.$note->id);

        // then the note content is returned
        $response->assertOk()
            ->assertJsonFragment(['content' => 'Display this note']);
    }

    public function test_can_update_own_note()
    {
        // given an existing note owned by the user
        $note = Note::factory()->create([
            'noteable_id' => $this->familyProfile->id,
            'noteable_type' => FamilyProfile::class,
            'user_id' => $this->user->id,
            'content' => 'Original content',
        ]);

        // when updating the note
        $response = $this->withHeaders($this->headers())
            ->putJson('/api/notes/'.$note->id, [
                'content' => 'Updated content',
            ]);

        // then the note is updated
        $response->assertOk();
        $this->assertDatabaseHas('notes', ['content' => 'Updated content']);
    }

    public function test_cannot_update_note_owned_by_another_user()
    {
        // given a note owned by another user
        $otherUser = User::factory()->create();
        $note = Note::factory()->create([
            'noteable_id' => $this->familyProfile->id,
            'noteable_type' => FamilyProfile::class,
            'user_id' => $otherUser->id,
        ]);

        // when updating the note
        $response = $this->withHeaders($this->headers())
            ->putJson('/api/notes/'.$note->id, [
                'content' => 'Hacked content',
            ]);

        // then access is denied
        $response->assertForbidden();
    }

    public function test_can_delete_own_note()
    {
        // given a note owned by the user
        $note = Note::factory()->create([
            'noteable_id' => $this->familyProfile->id,
            'noteable_type' => FamilyProfile::class,
            'user_id' => $this->user->id,
        ]);

        // when deleting the note
        $response = $this->withHeaders($this->headers())
            ->deleteJson('/api/notes/'.$note->id);

        // then the note is deleted
        $response->assertOk();
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_cannot_delete_note_owned_by_another_user()
    {
        // given a note owned by another user
        $otherUser = User::factory()->create();
        $note = Note::factory()->create([
            'noteable_id' => $this->familyProfile->id,
            'noteable_type' => FamilyProfile::class,
            'user_id' => $otherUser->id,
        ]);

        // when deleting the note
        $response = $this->withHeaders($this->headers())
            ->deleteJson('/api/notes/'.$note->id);

        // then access is denied
        $response->assertForbidden();
    }
}
