<?php

namespace App\Http\Controllers;

use App\Exports\UserExport;
use App\Mail\LoginMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $query = User::query()->where('role', 'employee');

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('position', 'like', '%'.$request->q.'%');
            });
        }

        $users = $query->select('id', 'name', 'position')->limit(10)->get();
        return response()->json([
            'message' => 'Search result',
            'data' => $users,
        ]);
    }

    public function index(Request $request)
    {
        $per_page = request()->input('per_page');
        $users = User::latest()->paginate($per_page);

        $query = User::query();
        if ($request->search) {
            $query->where('role', 'like', '%'.$request->search.'%')
                ->orWhere('position', 'like', '%'.$request->search.'%');
        }

        $users = $query->paginate($per_page);

        return response()->json([
            'message' => 'User data has been successfully retrieved',
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,project_manager,employee',
            'position' => 'nullable|required_if:role,employee|in:frontend,backend,uiux,mobile,quality_assurance',
        ], [
            'name.required' => 'Please fill in the name',
            'name.min' => 'Name must contain at least 3 letters',
            'email.required' => 'Please fill in the email',
            'email.unique' => 'Email has already been registered, use another email address',
            'email.email' => 'Invalid email format',
            'role.required' => 'Please choose at least 1 role',
            'role.in' => 'Selected role is invalid',
            'position.required_if' => 'Select one position for employee',
            'position.in' => 'Theres no position related',
        ]);

        $generatedPassword = Str::random(8);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'position' => $request->role == 'employee' ? $request->position : null,
            'password' => Hash::make($generatedPassword),
        ]);

        Mail::to($user->email)->send(new LoginMail($user->name, $user->email, $generatedPassword));

        return response()->json([
            'message' => 'User created successfully',
            'generated_password' => $generatedPassword,
            'data' => $user,
        ], 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'data' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'name' => 'sometimes|min:3',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'role' => 'sometimes|in:admin,project_manager,employee',
            'position' => 'sometimes|required_if:role,employee|in:frontend,backend,uiux,mobile,quality_assurance',
        ]);

        if ($user->role == 'admin' && $request->role != 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'cannot change the last admin role',
                ], 422);
            }
        }

        if ($user->email != $request->email) {
            $newPassword = Str::random(8);
            $user->password = Hash::make($newPassword);
        }

        $user->update($request->only([
            'name',
            'email',
            'role',
            'position',
        ]));

        return response()->json([
            'message' => 'User updated successfully',
            'generate_password' => isset($newPassword) ? $newPassword : null,
            'data' => $user,
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(new UserExport, 'users.xlsx');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
