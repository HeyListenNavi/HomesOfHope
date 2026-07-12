<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_get_token()
    {
        // given a registered user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // when logging in with valid credentials
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // then a token is returned
        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        // given a registered user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // when logging in with wrong password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // then validation fails with credentials error
        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_logout()
    {
        // given an authenticated user with a token
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // when logging out
        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->postJson('/api/logout');

        // then logout succeeds
        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_logout()
    {
        // given no authentication

        // when logging out
        $response = $this->postJson('/api/logout');

        // then access is denied
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_get_their_info()
    {
        // given an authenticated user
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // when fetching the current user
        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->getJson('/api/user');

        // then the user info is returned
        $response->assertOk()
            ->assertJson(['id' => $user->id, 'email' => $user->email]);
    }

    public function test_unauthenticated_user_cannot_get_user_info()
    {
        // given no authentication

        // when fetching the current user
        $response = $this->getJson('/api/user');

        // then access is denied
        $response->assertUnauthorized();
    }
}
