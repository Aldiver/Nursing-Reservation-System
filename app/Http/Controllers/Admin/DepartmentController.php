<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
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
        $departments = Department::all(['id', 'name', 'created_at'])->map(function ($department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'created_at' => $department->created_at->toDateString(),
            ];
        });

        // Define columns
        $columns = [
            // ['label' => 'ID', 'field' => 'id'],
            ['label' => 'Name', 'field' => 'name'],
            ['label' => 'Created At', 'field' => 'created_at'],
        ];

        // Get user permissions (if needed)
        $permissions = [
            'edit' => auth()->user()->can('edit'),
            'delete' => auth()->user()->can('delete'),
        ];

        return inertia('Admin/Department/Index', [
            'departments' => $departments,
            'columns' => $columns,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Admin/Department/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Department::create($request->all());

        return redirect()->route('admin.departments.index')->with('message', __('Department created successfully'));
        ;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        return inertia('Admin/Department/Edit', ['department' => $department]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $department->update($request->all());

        return redirect()->route('admin.departments.index')->with('message', __('Department updated successfully'));
        ;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('admin.departments.index');
    }
}
