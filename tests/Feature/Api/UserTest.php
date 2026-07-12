<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->token = $this->admin->createToken('test')->plainTextToken;
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    public function test_unauthenticated_user_cannot_access_users()
    {
        // given no authentication

        // when fetching users
        $response = $this->getJson('/api/users');

        // then access is denied
        $response->assertUnauthorized();
    }

    public function test_can_list_users()
    {
        // given existing users
        User::factory()->count(2)->create();

        // when fetching the list
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/users');

        // then all users are returned 
        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_user()
    {
        // given user data

        // when creating a user
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        // then the user is created
        $response->assertCreated()
            ->assertJsonFragment(['email' => 'newuser@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_can_show_user()
    {
        // given an existing user
        $user = User::factory()->create(['name' => 'Specific User']);

        // when fetching the user
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/users/'.$user->id);

        // then the user details are returned
        $response->assertOk()
            ->assertJsonFragment(['name' => 'Specific User']);
    }

    public function test_can_update_user()
    {
        // given an existing user
        $user = User::factory()->create(['name' => 'Original Name']);

        // when updating the user
        $response = $this->withHeaders($this->headers())
            ->putJson('/api/users/'.$user->id, [
                'name' => 'Updated Name',
            ]);

        // then the user is updated
        $response->assertOk();
        $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
    }

    public function test_can_delete_user()
    {
        // given an existing user
        $user = User::factory()->create();

        // when deleting the user
        $response = $this->withHeaders($this->headers())
            ->deleteJson('/api/users/'.$user->id);

        // then the user is deleted
        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
