<?php
namespace Tests\Feature\Services;
use App\Models\Applicant;
use App\Models\Group;
use App\Services\Applicant\AssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AssignmentServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_assign_applicant_to_group_picks_group_with_capacity()
    {
        // given a group with capacity and an applicant without group
        $group = Group::factory()->create(['capacity' => 10, 'date_time' => now()->addDays(1)]);
        $applicant = Applicant::factory()->create(['group_id' => null]);
        $service = new AssignmentService();

        // when assigning the applicant to a group
        $assignedGroup = $service->assignToGroup($applicant);

        // then applicant is assigned to the available group
        $this->assertEquals($group->id, $assignedGroup->id);
        $this->assertEquals($group->id, $applicant->group_id);
    }
    public function test_assign_applicant_creates_new_group_if_none_available()
    {
        // given an applicant without group and no available groups
        $applicant = Applicant::factory()->create(['group_id' => null]);
        $service = new AssignmentService();

        // when assigning the applicant to a group
        $assignedGroup = $service->assignToGroup($applicant);

        // then a new group is created and assigned
        $this->assertNotNull($assignedGroup);
        $this->assertEquals('pending', $applicant->confirmation_status);
    }
}
