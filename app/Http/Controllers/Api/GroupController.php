<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Group;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Get a list of active and future groups.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Group::class);

        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'date' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'min_date' => ['sometimes', 'date', 'date_format:Y-m-d'],
        ]);

        $query = Group::query()
            ->where('is_active', true)
            ->orderBy('date_time', 'asc');

        if (isset($validated['date'])) {
            $query->whereDate('date_time', $validated['date']);
        }

        if (isset($validated['min_date'])) {
            $query->whereDate('date_time', '>=', $validated['min_date']);
        }

        return $query->paginate($validated['limit'] ?? 20);
    }

    /**
     * Get a specific group details.
     */
    public function show(Group $group)
    {
        $this->authorize('view', $group);

        return $group;
    }

    /**
     * Get the applicants for a specific group, including their attendance status.
     */
    public function applicants(Request $request, Group $group)
    {
        $this->authorize('view', $group);

        $validated = $request->validate([
            'search' => 'nullable|string',
            'limit' => ['sometimes', 'integer', 'min:1'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $query = $group->applicants()->with(['currentAttendance']);

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('applicant_name', 'like', "%{$search}%")
                    ->orWhere('curp', 'like', "%{$search}%")
                    ->orWhere('chat_id', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($validated['limit'] ?? 20);

        return $paginator;
    }

    /**
     * Close the attendance for a group, marking pending applicants as absent.
     */
    public function closeAttendance(AttendanceService $attendanceService, Group $group)
    {
        $this->authorize('update', $group);

        $attendanceService->close($group);

        return response()->json([
            'message' => 'Attendance has been closed successfully.',
            'group' => $group,
        ]);
    }
}
