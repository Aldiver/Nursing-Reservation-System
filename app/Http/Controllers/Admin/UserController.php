<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:list', ['only' => ['index', 'show']]);
        $this->middleware('can:create', ['only' => ['create', 'store']]);
        $this->middleware('can:edit', ['only' => ['index', 'edit', 'update']]);
        $this->middleware('can:delete', ['only' => ['destroy']]);
    }
    public function index()
    {
        $users = User::with('roles')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'contact_number' => $user->contact_number,
                'role' => $user->roles->pluck('name')->implode(', '), // Retrieve roles
                'created_at' => $user->created_at->toDateString(),
            ];
        });

        // Define columns
        $columns = [
            // ['label' => 'ID', 'field' => 'id'],
            ['label' => 'Name', 'field' => 'name'],
            ['label' => 'Email', 'field' => 'email'],
            ['label' => 'Contact Number', 'field' => 'contact_number'],
            ['label' => 'Role', 'field' => 'role'],
            ['label' => 'Created At', 'field' => 'created_at'],
        ];

        // Get user permissions
        $permissions = [
            'edit' => auth()->user()->can('edit'),
            'delete' => auth()->user()->can('delete'),
        ];

        return inertia('Admin/User/Index', [
            'users' => $users,
            'columns' => $columns,
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'label' => $role->name,
            ];
        });

        $departments = Department::all()->map(function ($department) {
            return [
                'id' => $department->id,
                'label' => $department->name,
            ];
        });
        $permissions = Permission::all()->pluck("name", "id");
        return inertia('Admin/User/Create', [
            'roles' => $roles,
            'permissions' => $permissions,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'department' => 'required|string|exists:departments,name',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|exists:roles,name',
        ]);

        $department = Department::where('name', $request->department)->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'contact' => $request->contact,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        if ($request->has('permissions')) {
            // Fetch permission models based on IDs
            $permissions = Permission::whereIn('id', $request->permissions)->get();

            // Give permissions to the user
            $user->givePermissionTo($permissions);
        }

        $user->department()->associate($department);
        $user->save();

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRole = $user->roles->pluck('name')->first();
        return inertia('Admin/User/Edit', ['user' => $user, 'roles' => $roles, 'userRole' => $userRole]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
        ]);

        $user->syncRoles($request->role);

        return redirect()->route('admin.users.index');
    }
}
