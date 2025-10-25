<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Get all tasks for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = Task::forAssignee($user->id)
            ->with(['creator', 'assignee'])
            ->orderBy('due_date', 'asc');

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->byStatus($request->status);
        }

        if ($request->has('priority') && $request->priority !== 'all') {
            $query->byPriority($request->priority);
        }

        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        $tasks = $query->get();

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * Create a new task.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date|after_or_equal:today',
            'priority' => 'required|in:low,medium,high',
            'assignee_email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find assignee by email
            $assignee = User::where('email', $request->assignee_email)->first();
            
            if (!$assignee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignee not found'
                ], 404);
            }

            $task = Task::create([
                'creator_id' => Auth::id(),
                'assignee_id' => $assignee->id,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
            ]);

            $task->load(['creator', 'assignee']);

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => $task
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific task.
     */
    public function show(Task $task): JsonResponse
    {
        $user = Auth::user();

        // Check if user is assigned to this task
        if ($task->assignee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to task'
            ], 403);
        }

        $task->load(['creator', 'assignee']);

        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    /**
     * Update a task.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $user = Auth::user();

        // Check if user is assigned to this task
        if ($task->assignee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to task'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|required|date',
            'priority' => 'sometimes|required|in:low,medium,high',
            'is_completed' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = $request->only(['title', 'description', 'due_date', 'priority', 'is_completed']);

            // Handle completion status
            if ($request->has('is_completed')) {
                if ($request->is_completed && !$task->is_completed) {
                    $updateData['completed_at'] = now();
                } elseif (!$request->is_completed && $task->is_completed) {
                    $updateData['completed_at'] = null;
                }
            }

            $task->update($updateData);
            $task->load(['creator', 'assignee']);

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => $task
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $user = Auth::user();

        // Check if user is creator or assignee
        if ($task->creator_id !== $user->id && $task->assignee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to task'
            ], 403);
        }

        try {
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle task completion status.
     */
    public function toggleComplete(Task $task): JsonResponse
    {
        $user = Auth::user();

        // Check if user is assigned to this task
        if ($task->assignee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to task'
            ], 403);
        }

        try {
            $task->update([
                'is_completed' => !$task->is_completed,
                'completed_at' => !$task->is_completed ? now() : null,
            ]);

            $task->load(['creator', 'assignee']);

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully',
                'data' => $task
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task status update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get task statistics for the authenticated user.
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        
        $total = Task::forAssignee($user->id)->count();
        $done = Task::forAssignee($user->id)->where('is_completed', true)->count();
        $missed = Task::forAssignee($user->id)
            ->where('is_completed', false)
            ->where('due_date', '<', now()->toDateString())
            ->count();
        $dueToday = Task::forAssignee($user->id)
            ->where('is_completed', false)
            ->where('due_date', now()->toDateString())
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'done' => $done,
                'missed' => $missed,
                'due_today' => $dueToday,
            ]
        ]);
    }
}


