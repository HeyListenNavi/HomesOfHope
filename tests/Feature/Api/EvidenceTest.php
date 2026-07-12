<?php

namespace Tests\Feature\Api;

use App\Models\Evidence;
use App\Models\FamilyProfile;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    private Visit $visit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
        $familyProfile = FamilyProfile::factory()->create();
        $this->visit = Visit::factory()->create(['family_profile_id' => $familyProfile->id]);

        Storage::fake('public');
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    public function test_unauthenticated_user_cannot_access_evidence()
    {
        // given no authentication

        // when fetching evidence
        $response = $this->getJson('/api/evidence/1');

        // then access is denied
        $response->assertUnauthorized();
    }

    public function test_can_upload_evidence()
    {
        // given an image
        $image = UploadedFile::fake()->image('evidence.jpg', 800, 600);

        // when uploading evidence
        $response = $this->withHeaders($this->headers())
            ->post('/api/evidence', [
                'visit_id' => $this->visit->id,
                'image' => $image,
                'description' => 'Photo of the visit',
            ]);

        // then the evidence is stored
        $response->assertCreated();
        $this->assertDatabaseHas('evidence', [
            'visit_id' => $this->visit->id,
            'description' => 'Photo of the visit',
        ]);
    }

    public function test_can_show_evidence()
    {
        // given an existing evidence record with a file
        $image = UploadedFile::fake()->image('photo.jpg', 800, 600);
        $path = $image->store('evidence', 'public');

        $evidence = Evidence::factory()->create([
            'visit_id' => $this->visit->id,
            'file_path' => $path,
            'description' => 'Visit photo',
            'taken_by' => $this->user->id,
        ]);

        // when fetching the evidence
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/evidence/'.$evidence->id);

        // then the evidence details are returned
        $response->assertOk()
            ->assertJsonFragment(['description' => 'Visit photo']);
    }

    public function test_can_delete_evidence()
    {
        // given an existing evidence record with a file on disk
        $image = UploadedFile::fake()->image('delete-me.jpg', 800, 600);
        $path = $image->store('evidence', 'public');

        $evidence = Evidence::factory()->create([
            'visit_id' => $this->visit->id,
            'file_path' => $path,
            'taken_by' => $this->user->id,
        ]);

        // when deleting the evidence
        $response = $this->withHeaders($this->headers())
            ->deleteJson('/api/evidence/'.$evidence->id);

        // then the evidence is deleted from database
        $response->assertOk();
        $this->assertDatabaseMissing('evidence', ['id' => $evidence->id]);
    }

    public function test_upload_requires_valid_image()
    {
        // given no image

        // when uploading without an image
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/evidence', [
                'visit_id' => $this->visit->id,
            ]);

        // then validation fails
        $response->assertStatus(422);
    }
}
