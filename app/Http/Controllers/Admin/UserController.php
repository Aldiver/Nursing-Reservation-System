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
        $this->authorizeResource(User::class, 'user');
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
            'show' => auth()->user()->can('show'),
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
            'contact_number' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'department' => 'required|string|exists:departments,name',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|exists:roles,name',
        ]);

        $department = Department::where('name', $request->department)->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
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

        return redirect()->route('admin.users.index')->with('message', __('User created successfully'));
        ;
    }
    public function show(User $user)
    {
        $user->load('roles', 'department');
        return inertia('Admin/User/Show', [
            'user' => $user]);
    }
    public function edit(User $user)
    {
        // Check if the user trying to delete is the super admin
        if ($user->email === 'superadmin@example.com') {
            return redirect()->back()
                         ->with('message', __('Cannot edit super admin user.'));
        }
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
        return inertia('Admin/User/Edit', [
                'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'contact_number' => $user->contact_number,
                'email' => $user->email,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id ?? null,
                        'label' => $role->name ?? null,
                    ];
                }),
                'permissions' => $user->permissions->pluck('name', 'id'),
                'department' => [
                    'id' => $user->department->id ?? null,
                    'label' => $user->department->name ?? null,
                ],
            ],
            'roles' => $roles,
            'permissions' => $permissions,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'role' => 'required|string|exists:roles,name',
            'department' => 'required|string|exists:departments,name',
        ]);

        $department = Department::where('name', $request->department)->first();

        $user->update([
            'name' => $request->name,
            'contact_number' => $request->contact_number,
        ]);

        $user->syncRoles($request->role);

        // $user->assignRole($request->role);

        if ($request->has('permissions')) {
            // Fetch permission models based on IDs
            $permissions = Permission::whereIn('id', $request->permissions)->get();

            // Give permissions to the user
            $user->syncPermissions($permissions);
        }

        $user->department()->associate($department);
        $user->save();

        return redirect()->route('admin.users.index')->with('message', __('User updated successfully'));
        ;
    }

    public function destroy(User $user)
    {
        // Check if the user trying to delete is the super admin
        if ($user->email === 'superadmin@example.com') {
            return redirect()->back()
                         ->with('error', __('Cannot delete super admin user.'));
        }

        // Proceed with deleting the user if not the super admin
        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('message', __('User deleted successfully'));
    }
}
