<?php

namespace App\Http\Controllers\Api;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    /**
     * Scan QR code payload to mark attendance.
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'attendance_code' => 'required|string',
            'status' => ['nullable', Rule::enum(AttendanceStatus::class)],
        ]);

        $attendance = Attendance::with(['applicant', 'group'])
            ->where('attendance_code', $validated['attendance_code'])
            ->first();

        if (! $attendance) {
            return response()->json([
                'message' => 'Invalid or not found attendance code.',
            ], 404);
        }

        $attendance->update([
            'status' => $validated['status'] ?? AttendanceStatus::Present,
            'scanned_at' => now(),
        ]);

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

        $attendance->update([
            'status' => $validated['status'],
            'scanned_at' => now(),
        ]);

        return response()->json([
            'message' => 'Attendance updated manually.',
            'data' => $attendance->load('applicant'),
        ]);
    }
}
