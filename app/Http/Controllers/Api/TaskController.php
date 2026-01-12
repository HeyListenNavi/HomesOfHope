<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * List tasks. Filter by visit_id or assignee.
     */
    public function index(Request $request)
    {
        $query = Task::with(['visit', 'assignee:id,name']);

        if ($request->has('visit_id')) {
            $query->where('visit_id', $request->visit_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|string|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Automáticamente asignamos 'assigned_by' al usuario actual
        $data = $validated;
        $data['assigned_by'] = $request->user()->id;
        $data['status'] = 'pending';

        $task = Task::create($data);

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task
        ], 201);
    }

    public function show(string $id)
    {
        $task = Task::with(['visit', 'assignee', 'notes'])->findOrFail($id);
        return response()->json($task);
    }

    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'status' => 'sometimes|string|in:pending,in_progress,completed,cancelled',
            'priority' => 'sometimes|string',
            'due_date' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Si marcan completed, llenar completed_at automáticamente si no lo mandan
        if (isset($validated['status']) && $validated['status'] === 'completed' && empty($validated['completed_at'])) {
            $validated['completed_at'] = now();
        }

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task
        ]);
    }

    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}