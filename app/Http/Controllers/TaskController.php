<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function weeklyCompletedTask()
    {
        $tasks = Task::where('assignee_id', Auth::id())
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->selectRaw('DAYNAME(completed_at) as day')
            ->selectRaw('COUNT(*) as completed')
            ->groupBy('day')
            ->get();

        return response()->json([
            'message' => 'success',
            'data' => $tasks,
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
        $query = Task::query();
        if ($user->role === 'employee') {
            $query->where('assignee_id', $user->id);
        }
        if ($user->role === 'project_manager') {
            $query->whereHas('project', function ($q) use ($user) {
                $q->where('project_manager_id', $user->id);
            });
        }

        return response()->json([
            'data' => $query->with('attachments')->get(),
        ]);
    }

    public function show_detail($id)
    {
        $user = Auth::user();

        $task = Task::with(['attachments', 'project', 'assignee'])->find($id);

        if (! $task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        if ($user->role === 'employee') {
            if ($task->assignee_id !== $user->id) {
                return response()->json([
                    'message' => 'no access',
                ], 403);
            }
        } elseif ($user->role === 'project_manager') {
            if ($task->project->project_manager_id !== $user->id) {
                return response()->json([
                    'message' => 'no access',
                ], 403);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized role',
            ], 403);
        }

        return response()->json([
            'message' => 'Task detail',
            'data' => $task,
        ]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'assignee_id' => 'required|exists:users,id',
            'name' => 'required|min:3',
            'notes' => 'nullable|min:3',
            'priority' => 'required|in:high,medium,low',
            'due_date' => 'required|date',
            'completed_at' => 'nullable|date',
            'revision_notes' => 'nullable|string|min:10',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);

        $task = Task::create([
            'project_id' => $request->project_id,
            'assignee_id' => $request->assignee_id,
            'name' => $request->name,
            'notes' => $request->notes,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);

        if ($request->hasFile('image')) {

            $image = $request->file('image');

            $imageName = Str::random(20)
                .'-image.'
                .$image->getClientOriginalExtension();

            $path = $image->storeAs(
                'attachments',
                $imageName,
                'public'
            );

            Attachment::create([
                'task_id' => $task->id,
                'image' => $path,
            ]);
        }

        return response()->json([
            'message' => 'Task successfully added',
            'data' => $task->load('attachments'),
        ], 201);
    }

    public function update(Request $request, Task $task)
    {
        if (Auth::user()->role === 'employee' && $task->assignee_id !== Auth::id()) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }
        $request->validate([
            'name' => 'sometimes|min:3',
            'notes' => 'sometimes|nullable|min:3',
            'priority' => 'sometimes|in:high,medium,low',
            'due_date' => 'sometimes|date',
            'completed_at' => 'sometimes|nullable|date',
            'revision_notes' => 'sometimes|nullable|string|min:10',
            'status' => 'sometimes|in:todo,progress,completed',
            'review_status' => 'sometimes|in:pending,revision,approved',
            'image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);
        if (Auth::user()->role == 'employee') {
            $task->update($request->only([
                'status',
            ]));
        } elseif (Auth::user()->role == 'project_manager') {
            $task->update($request->only([
                'name',
                'notes',
                'priority',
                'due_date',
                'review_status',
                'revision_notes',
            ]));
            if ($request->review_status == 'approved') {
                $task->completed_at = now();
                $task->save();
            }
        }
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = Str::random(20)
                .'-image.'
                .$image->getClientOriginalExtension();
            $path = $image->storeAs(
                'attachments',
                $imageName,
                'public'
            );
            Attachment::create([
                'task_id' => $task->id,
                'image' => $path,
            ]);
        }

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task->load('attachments'),
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        $task = Task::with(['attachments', 'project', 'assignee'])->find($id);

        if (! $task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        if ($user->role === 'employee') {
            if ($task->assignee_id !== $user->id) {
                return response()->json([
                    'message' => 'no access',
                ], 403);
            }
        } elseif ($user->role === 'project_manager') {
            if (!$task->project || $task->project->project_manager_id !== $user->id) {
                return response()->json([
                    'message' => 'no access',
                ], 403);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized role',
            ], 403);
        }

        return response()->json([
            'message' => 'Task detail',
            'data' => $task,
        ]);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    public function trash()
    {
        $tasks = Task::onlyTrashed()->get();

        return response()->json([
            'message' => 'Get data successfully',
            'data' => $tasks,
        ]);
    }

    public function restore($id)
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $task->restore();

        return response()->json([
            'message' => 'Restore',
            'data' => $task,
        ]);
    }

    public function deletePermanent($id)
    {
        $task = Task::onlyTrashed()->where('id', $id)->first();

        if (!$task) {
            return response()->json([
                'message' => 'Data not found',
            ]);
        }

        $task->attachments()->forceDelete();
        $task->forceDelete();

        return response()->json([
            'message' => 'Task deleted Permanent',
        ]);
    }
}
