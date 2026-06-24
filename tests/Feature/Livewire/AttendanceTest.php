<?php

namespace Tests\Feature\Livewire;

use App\Enums\AttendanceStatus;
use App\Livewire\AttendancePage;
use App\Models\Applicant;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    public function test_applicant_has_attendance_relation()
    {
        // given an applicant with attendance
        $applicant = Applicant::factory()->create();
        $attendance = Attendance::factory()->create(['applicant_id' => $applicant->id]);

        // when accessing the attendance relation
        $relatedAttendance = $applicant->attendance;

        // then it returns the correct attendance record
        $this->assertInstanceOf(Attendance::class, $relatedAttendance);
        $this->assertEquals($attendance->id, $relatedAttendance->id);
    }

    public function test_group_assignation_creates_attendance_record()
    {
        // given an applicant and an active group with space
        $applicant = Applicant::factory()->create(['group_id' => null]);
        $group = Group::factory()->create([
            'is_active' => true,
            'capacity' => 10,
            'current_members_count' => 0,
            'date_time' => now()->addDays(2),
        ]);

        // when assigning the applicant to the group via the controller
        $url = URL::temporarySignedRoute('group.selection.assign', now()->addDays(3), ['applicant' => $applicant->id]);
        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post($url, [
                'group_id' => $group->id,
            ]);

        // then an attendance record is created with the correct data
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'applicant_id' => $applicant->id,
            'group_id' => $group->id,
            'status' => AttendanceStatus::Pending,
        ]);
        $this->assertNotNull($applicant->refresh()->group_id);
    }

    public function test_attendance_page_processes_valid_code()
    {
        // given a group, an applicant with a code, and a user
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);
        $attendance = Attendance::factory()->create([
            'applicant_id' => $applicant->id,
            'group_id' => $group->id,
            'attendance_code' => 'VALID123',
            'status' => AttendanceStatus::Pending,
        ]);

        // when processing the valid code via the Livewire component
        Livewire::actingAs($user)
            ->test(AttendancePage::class, ['group' => $group])
            ->set('scanCode', 'VALID123')
            ->call('processCode');

        // then attendance is marked as present and scanned_at is set
        $attendance->refresh();
        $this->assertEquals(AttendanceStatus::Present, $attendance->status);
        $this->assertNotNull($attendance->scanned_at);
    }

    public function test_attendance_page_rejects_code_from_other_group()
    {
        // given two groups and an applicant in group B
        $user = User::factory()->create();
        $groupA = Group::factory()->create();
        $groupB = Group::factory()->create();
        $applicant = Applicant::factory()->create(['group_id' => $groupB->id]);
        $attendance = Attendance::factory()->create([
            'applicant_id' => $applicant->id,
            'group_id' => $groupB->id,
            'attendance_code' => 'GROUPBCODE',
            'status' => AttendanceStatus::Pending,
        ]);

        // when trying to process group B's code in group A's attendance page
        Livewire::actingAs($user)
            ->test(AttendancePage::class, ['group' => $groupA])
            ->set('scanCode', 'GROUPBCODE')
            ->call('processCode')
            ->assertSet('scanResult', 'warning')
            ->assertSet('scanMessage', 'Pertenece a otro grupo.');

        // then attendance remains pending
        $attendance->refresh();
        $this->assertEquals(AttendanceStatus::Pending, $attendance->status);
    }

    public function test_close_attendance_marks_remaining_as_absent()
    {
        // given a group with one present and one pending applicant
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $applicant1 = Applicant::factory()->create(['group_id' => $group->id]);
        Attendance::factory()->present()->create(['applicant_id' => $applicant1->id, 'group_id' => $group->id]);
        $applicant2 = Applicant::factory()->create(['group_id' => $group->id]);
        Attendance::factory()->create(['applicant_id' => $applicant2->id, 'group_id' => $group->id, 'status' => AttendanceStatus::Pending]);

        // when closing attendance
        Livewire::actingAs($user)
            ->test(AttendancePage::class, ['group' => $group])
            ->call('closeAttendance');

        // then pending applicant is marked as absent and group is closed
        $this->assertEquals(AttendanceStatus::Absent, $applicant2->attendance->refresh()->status);
        $this->assertNotNull($group->refresh()->attendance_closed_at);
    }
}
