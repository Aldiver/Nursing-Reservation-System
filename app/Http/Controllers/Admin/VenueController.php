<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venue;

class VenueController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:list', ['only' => ['index', 'show']]);
        $this->middleware('can:create', ['only' => ['create', 'store']]);
        $this->middleware('can:edit', ['only' => ['index', 'edit', 'update']]);
        $this->middleware('can:delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $venues = Venue::all(['id', 'name', 'description', 'created_at'])->map(function ($venue) {
            return [
                'id' => $venue->id,
                'name' => $venue->name,
                'location' => $venue->description,
                'created_at' => $venue->created_at->toDateString(),
            ];
        });

        // Define columns
        $columns = [
            // ['label' => 'ID', 'field' => 'id'],
            ['label' => 'Name', 'field' => 'name'],
            ['label' => 'Location', 'field' => 'location'],
            ['label' => 'Created At', 'field' => 'created_at'],
        ];

        // Get user permissions (if needed)
        $permissions = [
            'edit' => auth()->user()->can('edit'),
            'delete' => auth()->user()->can('delete'),
        ];

        return inertia('Admin/Department/Index', [
            'departments' => $venues,
            'columns' => $columns,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Admin/Venue/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        Venue::create($request->all());

        return redirect()->route('admin.venues.index');
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
    public function edit(Venue $venue)
    {
        return inertia('Admin/Venues/Edit', ['venue' => $venue]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venue $venue)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $venue->update($request->all());

        return redirect()->route('admin.venues.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venue $venue)
    {
        $venue->delete();

        return redirect()->route('admin.venues.index');
    }
}
