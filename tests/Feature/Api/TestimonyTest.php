<?php

namespace Tests\Feature\Api;

use App\Models\FamilyProfile;
use App\Models\Testimony;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonyTest extends TestCase
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

    public function test_unauthenticated_user_cannot_access_testimonies()
    {
        // given no authentication

        // when fetching testimonies
        $response = $this->getJson('/api/testimonies');

        // then access is denied
        $response->assertUnauthorized();
    }

    public function test_can_list_testimonies()
    {
        // given existing testimonies 
        Testimony::create([
            'family_profile_id' => $this->familyProfile->id,
            'recorded_by' => $this->user->id,
            'recorded_at' => now(),
            'transcription' => 'Testimony 1',
        ]);
        Testimony::create([
            'family_profile_id' => $this->familyProfile->id,
            'recorded_by' => $this->user->id,
            'recorded_at' => now(),
            'transcription' => 'Testimony 2',
        ]);

        // when fetching the list
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/testimonies');

        // then all testimonies are returned 
        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_testimony()
    {
        // given testimony data

        // when creating a testimony
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/testimonies', [
                'family_profile_id' => $this->familyProfile->id,
                'language' => 'es',
                'transcription' => 'This is a testimony.',
                'summary' => 'Short summary.',
                'recorded_at' => now()->format('Y-m-d H:i:s'),
            ]);

        // then the testimony is created
        $response->assertCreated();
        $this->assertDatabaseHas('testimonies', ['transcription' => 'This is a testimony.']);
    }

    public function test_can_show_testimony()
    {
        // given an existing testimony
        $testimony = Testimony::create([
            'family_profile_id' => $this->familyProfile->id,
            'recorded_by' => $this->user->id,
            'recorded_at' => now(),
            'transcription' => 'Show this testimony',
        ]);

        // when fetching the testimony
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/testimonies/'.$testimony->id);

        // then the testimony is returned
        $response->assertOk()
            ->assertJsonFragment(['transcription' => 'Show this testimony']);
    }

    public function test_can_update_testimony()
    {
        // given an existing testimony
        $testimony = Testimony::create([
            'family_profile_id' => $this->familyProfile->id,
            'recorded_by' => $this->user->id,
            'recorded_at' => now(),
            'transcription' => 'Original content',
        ]);

        // when updating the testimony
        $response = $this->withHeaders($this->headers())
            ->putJson('/api/testimonies/'.$testimony->id, [
                'transcription' => 'Updated content',
            ]);

        // then the testimony is updated
        $response->assertOk();
        $this->assertDatabaseHas('testimonies', ['transcription' => 'Updated content']);
    }

    public function test_can_delete_testimony()
    {
        // given an existing testimony
        $testimony = Testimony::create([
            'family_profile_id' => $this->familyProfile->id,
            'recorded_by' => $this->user->id,
            'recorded_at' => now(),
            'transcription' => 'Delete me',
        ]);

        // when deleting the testimony
        $response = $this->withHeaders($this->headers())
            ->deleteJson('/api/testimonies/'.$testimony->id);

        // then the testimony is deleted
        $response->assertOk();
        $this->assertDatabaseMissing('testimonies', ['id' => $testimony->id]);
    }
}
