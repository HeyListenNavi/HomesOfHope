<?php

namespace Tests\Feature\Api;

use App\Models\FamilyProfile;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitTest extends TestCase
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

    public function test_unauthenticated_user_cannot_access_visits()
    {
        // given no authentication

        // when fetching visits
        $response = $this->getJson('/api/visits');

        // then access is denied
        $response->assertUnauthorized();
    }

    public function test_can_list_visits()
    {
        // given existing visits
        Visit::factory()->count(2)->create(['family_profile_id' => $this->familyProfile->id]);

        // when fetching the list
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/visits');

        // then all visits are returned
        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_visit()
    {
        // given visit data

        // when creating a visit
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/visits', [
                'family_profile_id' => $this->familyProfile->id,
                'visit_date' => now()->format('Y-m-d'),
                'location_type' => 'home',
                'scheduled_at' => now()->format('Y-m-d'),
                'status' => 'scheduled',
            ]);

        // then the visit is created
        $response->assertCreated();
        $this->assertDatabaseHas('visits', ['family_profile_id' => $this->familyProfile->id]);
    }

    public function test_can_show_visit()
    {
        // given an existing visit
        $visit = Visit::factory()->create(['family_profile_id' => $this->familyProfile->id]);

        // when fetching the visit
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/visits/'.$visit->id);

        // then the visit details are returned
        $response->assertOk()
            ->assertJsonFragment(['id' => $visit->id]);
    }

    public function test_can_update_visit()
    {
        // given an existing visit
        $visit = Visit::factory()->create([
            'family_profile_id' => $this->familyProfile->id,
            'outcome_summary' => 'Original summary',
        ]);

        // when updating the visit
        $response = $this->withHeaders($this->headers())
            ->putJson('/api/visits/'.$visit->id, [
                'outcome_summary' => 'Updated summary',
            ]);

        // then the visit is updated
        $response->assertOk();
        $this->assertDatabaseHas('visits', ['outcome_summary' => 'Updated summary']);
    }

    public function test_can_delete_visit()
    {
        // given an existing visit
        $visit = Visit::factory()->create(['family_profile_id' => $this->familyProfile->id]);

        // when deleting the visit
        $response = $this->withHeaders($this->headers())
            ->deleteJson('/api/visits/'.$visit->id);

        // then the visit is deleted
        $response->assertOk();
        $this->assertDatabaseMissing('visits', ['id' => $visit->id]);
    }
}
