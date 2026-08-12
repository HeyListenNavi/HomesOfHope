<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Get a list of active and future groups.
     */
    public function index(Request $request)
    {
        $query = Group::query()
            ->where('is_active', true)
            ->whereDate('date_time', '>=', now()->toDateString())
            ->orderBy('date_time', 'asc');

        return response()->json($query->paginate($request->input('limit', 20)));
    }

    /**
     * Get the applicants for a specific group, including their attendance status.
     */
    public function applicants(string $id)
    {
        $group = Group::findOrFail($id);

        $applicants = $group->applicants()
            ->with(['attendance'])
            ->get();

        return response()->json([
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'date_time' => $group->date_time,
            ],
            'applicants' => $applicants,
        ]);
    }
}
