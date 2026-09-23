<?php

namespace App\Http\Controllers\Api;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    /**
     * Scan QR code payload to mark attendance.
     */
    public function scan(Request $request, AttendanceService $attendanceService)
    {
        $validated = $request->validate([
            'attendance_code' => 'required|string',
        ]);

        $attendance = Attendance::with(['applicant', 'group'])
            ->where('attendance_code', $validated['attendance_code'])
            ->first();

        if (! $attendance) {
            return response()->json([
                'message' => 'Invalid or not found attendance code.',
            ], 404);
        }

        $result = $attendanceService->checkIn($attendance);

        if (! $result['success']) {
            return response()->json([
                'message' => $result['reason'] === 'closed'
                    ? 'Attendance is closed for this group.'
                    : 'Attendance has already been marked for this code.',
            ], $result['reason'] === 'closed' ? 423 : 409);
        }

        return response()->json([
            'message' => 'Attendance marked successfully.',
            'data' => [
                'attendance' => $attendance,
                'applicant_name' => $attendance->applicant->applicant_name,
                'group_name' => $attendance->group->name,
            ],
        ]);
    }

    /**
     * Manually update attendance status by ID.
     */
    public function update(Request $request, string $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(AttendanceStatus::class)],
        ]);

        if ($attendance->group && $attendance->group->attendance_closed_at) {
            return response()->json([
                'message' => 'Attendance is closed for this group.',
            ], 423);
        }

        $attendance->update([
            'status' => $validated['status'],
            'scanned_at' => match (AttendanceStatus::from($validated['status'])) {
                AttendanceStatus::Present, AttendanceStatus::Attended => $attendance->scanned_at ?? now(),
                default => null,
            },
        ]);

        return response()->json([
            'message' => 'Attendance updated manually.',
            'data' => $attendance->load('applicant'),
        ]);
    }
}
