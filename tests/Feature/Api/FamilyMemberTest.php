<?php

namespace Tests\Feature\Api;

use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyMemberTest extends TestCase
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

    public function test_unauthenticated_user_cannot_access_family_members()
    {
        // given no authentication

        // when fetching family members
        $response = $this->getJson('/api/family-members');

        // then access is denied
        $response->assertUnauthorized();
    }

    public function test_can_list_family_members()
    {
        // given existing family members
        FamilyMember::factory()->count(2)->create(['family_profile_id' => $this->familyProfile->id]);

        // when fetching the list
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/family-members');

        // then all members are returned (paginated)
        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_family_member()
    {
        // given family member data

        // when creating a family member
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/family-members', [
                'family_profile_id' => $this->familyProfile->id,
                'name' => 'Juan',
                'paternal_surname' => 'Pérez',
                'birth_date' => '1990-01-15',
                'relationship' => 'padre',
            ]);

        // then the member is created
        $response->assertCreated()
            ->assertJsonFragment(['name' => 'Juan']);
        $this->assertDatabaseHas('family_members', ['name' => 'Juan']);
    }

    public function test_can_show_family_member()
    {
        // given an existing family member
        $member = FamilyMember::factory()->create([
            'family_profile_id' => $this->familyProfile->id,
            'name' => 'María',
        ]);

        // when fetching the member
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/family-members/'.$member->id);

        // then the member details are returned
        $response->assertOk()
            ->assertJsonFragment(['name' => 'María']);
    }

    public function test_can_update_family_member()
    {
        // given an existing family member
        $member = FamilyMember::factory()->create([
            'family_profile_id' => $this->familyProfile->id,
            'name' => 'Original',
        ]);

        // when updating the member
        $response = $this->withHeaders($this->headers())
            ->putJson('/api/family-members/'.$member->id, [
                'name' => 'Updated',
            ]);

        // then the member is updated
        $response->assertOk();
        $this->assertDatabaseHas('family_members', ['name' => 'Updated']);
    }

    public function test_can_delete_family_member()
    {
        // given an existing family member
        $member = FamilyMember::factory()->create(['family_profile_id' => $this->familyProfile->id]);

        // when deleting the member
        $response = $this->withHeaders($this->headers())
            ->deleteJson('/api/family-members/'.$member->id);

        // then the member is deleted
        $response->assertOk();
        $this->assertDatabaseMissing('family_members', ['id' => $member->id]);
    }
}
