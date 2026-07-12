<?php

namespace Tests\Feature\Api;

use App\Models\FamilyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    public function test_unauthenticated_user_cannot_access_family_profiles()
    {
        // given no authentication

        // when fetching family profiles
        $response = $this->getJson('/api/family-profiles');

        // then access is denied
        $response->assertUnauthorized();
    }

    public function test_can_list_family_profiles()
    {
        // given existing family profiles
        FamilyProfile::factory()->count(3)->create();

        // when fetching the list
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/family-profiles');

        // then all profiles are returned 
        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_family_profile()
    {
        // given family profile data

        // when creating a family profile
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/family-profiles', [
                'family_name' => 'Pérez López',
                'status' => 'new',
                'current_address' => 'Calle Principal 123',
                'opened_at' => '2026-01-15',
            ]);

        // then the profile is created
        $response->assertCreated()
            ->assertJsonFragment(['family_name' => 'Pérez López']);
        $this->assertDatabaseHas('family_profiles', ['family_name' => 'Pérez López']);
    }

    public function test_can_show_family_profile()
    {
        // given an existing family profile
        $familyProfile = FamilyProfile::factory()->create(['family_name' => 'García Hernández']);

        // when fetching the profile
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/family-profiles/'.$familyProfile->id);

        // then the profile details are returned
        $response->assertOk()
            ->assertJsonFragment(['family_name' => 'García Hernández']);
    }

    public function test_can_update_family_profile()
    {
        // given an existing family profile
        $familyProfile = FamilyProfile::factory()->create(['family_name' => 'Original Name']);

        // when updating the profile
        $response = $this->withHeaders($this->headers())
            ->putJson('/api/family-profiles/'.$familyProfile->id, [
                'family_name' => 'Updated Name',
            ]);

        // then the profile is updated
        $response->assertOk();
        $this->assertDatabaseHas('family_profiles', ['family_name' => 'Updated Name']);
    }

    public function test_can_delete_family_profile()
    {
        // given an existing family profile
        $familyProfile = FamilyProfile::factory()->create();

        // when deleting the profile
        $response = $this->withHeaders($this->headers())
            ->deleteJson('/api/family-profiles/'.$familyProfile->id);

        // then the profile is deleted
        $response->assertOk();
        $this->assertDatabaseMissing('family_profiles', ['id' => $familyProfile->id]);
    }

    public function test_create_requires_family_name()
    {
        // given invalid data without family_name

        // when creating a family profile
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/family-profiles', []);

        // then validation fails
        $response->assertStatus(422);
    }
}
