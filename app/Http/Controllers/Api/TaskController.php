<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * List tasks. Filter by visit_id or assignee.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'visit_id' => ['required', 'integer', 'exists:visits,id'],
            'assigned_to' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::enum(TaskStatus::class)],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $this->authorize('view', Visit::findOrFail($validated['visit_id']));

        $query = Task::with(['visit', 'assignee'])
            ->where('visit_id', $validated['visit_id']);

        if (isset($validated['assigned_to'])) {
            $query->where('assigned_to', $validated['assigned_to']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return $query->paginate(20);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $this->authorize('update', Visit::findOrFail($validated['visit_id']));

        // Automáticamente asignamos 'assigned_by' al usuario actual
        $data = $validated;
        $data['assigned_by'] = $request->user()->id;
        $data['status'] = TaskStatus::Pending;

        $task = Task::create($data);

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task,
        ], 201);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task->visit);

        return $task->load(['visit', 'assignee', 'notes']);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task->visit);

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'status' => ['sometimes', Rule::enum(TaskStatus::class)],
            'priority' => ['sometimes', Rule::enum(TaskPriority::class)],
            'due_date' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if (isset($validated['status']) && $validated['status'] === TaskStatus::Completed->value && empty($validated['completed_at'])) {
            $validated['completed_at'] = now();
        }

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task,
        ]);
    }

    public function destroy(Task $task)
    {
        $this->authorize('update', $task->visit);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
