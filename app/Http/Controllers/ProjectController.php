<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page');
        $query = Project::query();

        if (Auth::user()->role === 'employee') {
            $query->whereHas('tasks', function ($q) {
                $q->where('assignee_id', Auth::id());
            })->with('tasks');
        } else {
            $query->where('project_manager_id', Auth::id())->with('tasks');
        }

        if ($request->search) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->sort_by) {
            $allowedSort = ['start_date', 'created_at'];

            if (in_array($request->sort_by, $allowedSort)) {
                $direction = $request->input('sort_direction', 'asc');
                $query->orderBy($request->sort_by, $direction);
            }
        }
        $projects = $query->paginate($per_page);

        return response()->json([
            'message' => 'Get project successfully',
            'data' => $projects->items(),
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'title' => 'required|string|min:3',
            'description' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'resource_links' => 'nullable',
        ], [
            'title.required' => 'Please fill the title',
            'title.min' => 'Fill at least 3 letters',
            'description.required' => 'Please fill the description so your project can be more explained',
            'start_date' => 'Add the start date',
            'start_date.after_or_equal' => 'Start date cannot be in the past',
        ]);

        $project = Project::create([
            'project_manager_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'resource_links' => $request->resource_links,
        ]);

        return response()->json([
            'message' => 'Project successfully added',
            'data' => $project,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        // cek cuma admin yang bisa update
        if (Auth::user()->role !== 'project_manager') {
            return response()->json([
                'message' => 'no access',
            ], 403);
        }

        // cek projekan yang dia doang
        if ($project->project_manager_id !== Auth::id()) {
            return response()->json([
                'message' => 'no accces',
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|min:3',
            'description' => 'sometimes|string',
            'start_date' => 'sometimes|date|after_or_equal:today',
            'resource_links' => 'nullable',
        ], [
            'title.min' => 'Fill at least 3 letters',
            'start_date.after_or_equal' => 'Start date cannot be in the past',
        ]);

        $project->update($request->only([
            'title',
            'description',
            'start_date',
            'resource_links',
        ]));

        return response()->json([
            'message' => 'Project updated successfully',
            'data' => $project,
        ]);
    }

    public function show(Project $project)
    {
        if (Auth::user()->role === 'employee') {
            // employee cuma boleh lihat project yang ada task dia
            $allowed = $project->tasks()
                ->where('assignee_id', Auth::id())
                ->exists();

            if (! $allowed) {
                return response()->json([
                    'message' => 'no access',
                ], 403);
            }
        } else {
            // project manager cuma boleh lihat project dia sendiri
            if ($project->project_manager_id !== Auth::id()) {
                return response()->json([
                    'message' => 'no access',
                ], 403);
            }
        }

        return response()->json([
            'message' => 'Project detail',
            'data' => $project->load('tasks.assignee', 'tasks.attachments'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        if ($project->tasks()->exists()) {

            return response()->json([
                'message' => 'Cannot delete this project because there are tasks related',
            ], 422);

        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully',
        ]);
    }

    public function trash()
    {
        $projects = Project::onlyTrashed()->get();

        return response()->json([
            'message' => 'Get project successfully',
            'data' => $projects,
        ]);
    }

    public function restore($id)
    {
        $project = Project::onlyTrashed()
            ->findOrFail($id);

        $project->restore();

        return response()->json([
            'message' => 'Project restored successfully',
            'data' => $project,
        ]);
    }

    public function deletePermanent($id)
    {
        $project = Project::onlyTrashed()
            ->findOrFail($id);

        if ($project->tasks()->exists()) {

            return response()->json([
                'message' => 'Cannot permanently delete project because there are tasks related',
            ], 422);

        }

        $project->forceDelete();

        return response()->json([
            'message' => 'Project permanently deleted',
        ]);
    }
}
