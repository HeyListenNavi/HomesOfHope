<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Applicant;
use App\Models\Attendance;
use App\Models\Group;
use App\Services\Group\GroupService;

class AttendanceService
{
    public function __construct(protected GroupService $groupService) {}

    public function enroll(Applicant $applicant, Group $group): Attendance
    {
        $attendance = $applicant->attendances()
            ->where('group_id', $group->id)
            ->first();

        if ($attendance) {
            $this->resetAttendance($attendance, $applicant);

            return $attendance;
        }

        return $applicant->attendances()->create([
            'group_id' => $group->id,
            'attendance_code' => $this->generateCode($applicant),
            'status' => AttendanceStatus::Pending,
        ]);
    }

    public function checkIn(Attendance $attendance): array
    {
        if ($attendance->group->attendance_closed_at) {
            return ['success' => false, 'reason' => 'closed'];
        }

        if (in_array($attendance->status, [AttendanceStatus::Present, AttendanceStatus::Attended], true)) {
            return ['success' => false, 'reason' => 'already', 'status' => $attendance->status];
        }

        $attendance->update([
            'status' => AttendanceStatus::Present,
            'scanned_at' => now(),
        ]);

        return ['success' => true, 'reason' => 'success', 'status' => AttendanceStatus::Present];
    }

    public function toggleAttended(Attendance $attendance): void
    {
        $attendance->update([
            'status' => $attendance->status === AttendanceStatus::Present
                ? AttendanceStatus::Attended
                : AttendanceStatus::Present,
        ]);
    }

    public function markPresent(Attendance $attendance): void
    {
        $attendance->update([
            'status' => AttendanceStatus::Present,
            'scanned_at' => $attendance->scanned_at ?? now(),
        ]);
    }

    public function markAttended(Attendance $attendance): void
    {
        $attendance->update([
            'status' => AttendanceStatus::Attended,
            'scanned_at' => $attendance->scanned_at ?? now(),
        ]);
    }

    public function markAbsent(Attendance $attendance): void
    {
        $attendance->update([
            'status' => AttendanceStatus::Absent,
            'scanned_at' => null,
        ]);
    }

    public function close(Group $group): void
    {
        if ($group->attendance_closed_at) {
            return;
        }

        $group->applicants()->get()->each(function (Applicant $applicant) use ($group) {
            $attendance = $applicant->attendances()
                ->where('group_id', $group->id)
                ->first();

            if (! $attendance || in_array($attendance->status, [AttendanceStatus::Pending, AttendanceStatus::Absent], true)) {
                $this->markAbsent($attendance ?? $this->enroll($applicant, $group));

                $applicant->update([
                    'group_id' => null,
                    'confirmation_status' => 'pending',
                ]);

                $this->groupService->sendRescheduleLink($applicant);
            }
        });

        $group->update(['attendance_closed_at' => now()]);
    }

    protected function resetAttendance(Attendance $attendance, Applicant $applicant): void
    {
        $attendance->update([
            'status' => AttendanceStatus::Pending,
            'scanned_at' => null,
            'attendance_code' => $this->generateCode($applicant),
        ]);
    }

    protected function generateCode(Applicant $applicant): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid($applicant->id, true)), 0, 8));
        } while (Attendance::where('attendance_code', $code)->exists());

        return $code;
    }
}
