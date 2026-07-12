<?php

namespace Tests\Feature\Api;

use App\Models\Colony;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColonyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_colonies_without_auth()
    {
        // given existing colonies
        Colony::factory()->count(3)->create();

        // when fetching the list
        $response = $this->getJson('/api/bot/colonies');

        // then all colonies are returned
        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_returns_active_colonies()
    {
        // given active and inactive colonies
        Colony::factory()->create([
            'name' => 'Active Colony',
            'city' => 'CityA',
            'is_active' => true,
        ]);
        Colony::factory()->create([
            'name' => 'Inactive Colony',
            'city' => 'CityB',
            'is_active' => false,
        ]);

        // when fetching the list
        $response = $this->getJson('/api/bot/colonies');

        // then only active colonies are returned
        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('CityA', ['Active Colony']);
    }
}
