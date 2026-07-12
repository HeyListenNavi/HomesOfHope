<?php

namespace Tests\Feature\Permission;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_access_inbox()
    {
        // given an admin user with applicant.view_any permission
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // when accessing the inbox page
        $response = $this->actingAs($admin)->get('/admin/inbox');

        // then it loads successfully
        $response->assertOk();
    }

    public function test_user_without_applicant_view_any_cannot_access_inbox()
    {
        // given a user without applicant.view_any
        $user = User::factory()->create();

        // when accessing the inbox page
        $response = $this->actingAs($user)->get('/admin/inbox');

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_access_bot_settings()
    {
        // given an admin user with bot_setting.view_any permission
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // when accessing the bot settings page
        $response = $this->actingAs($admin)->get('/admin/bot-settings');

        // then it loads successfully
        $response->assertOk();
    }

    public function test_user_without_bot_setting_view_any_cannot_access_bot_settings()
    {
        // given a user without bot_setting.view_any
        $user = User::factory()->create();

        // when accessing the bot settings page
        $response = $this->actingAs($user)->get('/admin/bot-settings');

        // then access is denied
        $response->assertForbidden();
    }
}
