<?php

namespace Tests\Feature\Api;

use App\Models\FamilyProfile;
use App\Models\Task;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
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
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    public function test_unauthenticated_user_cannot_access_tasks()
    {
        // given no authentication

        // when fetching tasks
        $response = $this->getJson('/api/tasks');

        // then access is denied
        $response->assertUnauthorized();
    }

    public function test_can_list_tasks()
    {
        // given existing tasks 
        Task::create([
            'visit_id' => $this->visit->id,
            'title' => 'Task 1',
            'assigned_by' => $this->user->id,
            'assigned_to' => $this->user->id,
        ]);
        Task::create([
            'visit_id' => $this->visit->id,
            'title' => 'Task 2',
            'assigned_by' => $this->user->id,
            'assigned_to' => $this->user->id,
        ]);

        // when fetching the list
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/tasks');

        // then all tasks are returned 
        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_task()
    {
        // given task data

        // when creating a task
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/tasks', [
                'visit_id' => $this->visit->id,
                'title' => 'Test task',
                'description' => 'Task description',
                'assigned_to' => $this->user->id,
                'priority' => 'medium',
                'status' => 'pending',
            ]);

        // then the task is created
        $response->assertCreated();
        $this->assertDatabaseHas('tasks', ['title' => 'Test task']);
    }

    public function test_can_show_task()
    {
        // given an existing task
        $task = Task::create([
            'visit_id' => $this->visit->id,
            'title' => 'Specific task',
            'assigned_by' => $this->user->id,
            'assigned_to' => $this->user->id,
        ]);

        // when fetching the task
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/tasks/'.$task->id);

        // then the task details are returned
        $response->assertOk()
            ->assertJsonFragment(['title' => 'Specific task']);
    }

    public function test_can_update_task()
    {
        // given an existing task
        $task = Task::create([
            'visit_id' => $this->visit->id,
            'title' => 'Original title',
            'assigned_by' => $this->user->id,
            'assigned_to' => $this->user->id,
        ]);

        // when updating the task
        $response = $this->withHeaders($this->headers())
            ->putJson('/api/tasks/'.$task->id, [
                'title' => 'Updated title',
                'status' => 'in_progress',
            ]);

        // then the task is updated
        $response->assertOk();
        $this->assertDatabaseHas('tasks', ['title' => 'Updated title']);
    }

    public function test_can_delete_task()
    {
        // given an existing task
        $task = Task::create([
            'visit_id' => $this->visit->id,
            'title' => 'Delete me',
            'assigned_by' => $this->user->id,
            'assigned_to' => $this->user->id,
        ]);

        // when deleting the task
        $response = $this->withHeaders($this->headers())
            ->deleteJson('/api/tasks/'.$task->id);

        // then the task is deleted
        $response->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
