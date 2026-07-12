<?php

namespace Tests\Feature\Api;

use App\Models\Document;
use App\Models\FamilyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
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

        Storage::fake('public');
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    public function test_unauthenticated_user_cannot_access_documents()
    {
        // given no authentication

        // when fetching a document
        $response = $this->getJson('/api/documents/1');

        // then access is denied
        $response->assertUnauthorized();
    }

    public function test_can_upload_document()
    {
        // given a fake file
        $file = UploadedFile::fake()->create('document.pdf', 100);

        // when uploading the document
        $response = $this->withHeaders($this->headers())
            ->post('/api/documents', [
                'documentable_id' => $this->familyProfile->id,
                'documentable_type' => 'family_profile',
                'document_type' => 'identification',
                'file' => $file,
            ]);

        // then the document is stored
        $response->assertCreated();
        $this->assertDatabaseHas('documents', [
            'documentable_id' => $this->familyProfile->id,
            'documentable_type' => FamilyProfile::class,
            'document_type' => 'identification',
            'original_name' => 'document.pdf',
        ]);
    }

    public function test_can_show_document()
    {
        // given an existing document with a real file
        $file = UploadedFile::fake()->create('doc.pdf', 100);
        $path = $file->store('documents', 'public');

        $document = Document::factory()->create([
            'documentable_id' => $this->familyProfile->id,
            'documentable_type' => FamilyProfile::class,
            'file_path' => $path,
            'original_name' => 'doc.pdf',
            'uploaded_by' => $this->user->id,
        ]);

        // when fetching the document
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/documents/'.$document->id);

        // then the document details are returned
        $response->assertOk()
            ->assertJsonFragment(['original_name' => 'doc.pdf']);
    }

    public function test_can_delete_document()
    {
        // given an existing document with a real file on disk
        $file = UploadedFile::fake()->create('deletable.pdf', 100);
        $path = $file->store('documents', 'public');

        $document = Document::factory()->create([
            'documentable_id' => $this->familyProfile->id,
            'documentable_type' => FamilyProfile::class,
            'file_path' => $path,
            'uploaded_by' => $this->user->id,
        ]);

        // when deleting the document
        $response = $this->withHeaders($this->headers())
            ->deleteJson('/api/documents/'.$document->id);

        // then the document is deleted from database
        $response->assertOk();
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_upload_requires_valid_file()
    {
        // given no file

        // when uploading without a file
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/documents', [
                'documentable_id' => $this->familyProfile->id,
                'documentable_type' => 'family_profile',
                'document_type' => 'identification',
            ]);

        // then validation fails
        $response->assertStatus(422);
    }
}
