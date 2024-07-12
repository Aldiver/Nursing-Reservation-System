<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venue;

class VenueController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Venue::class, 'venue');
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
            'show' => auth()->user()->can('show'),
            'edit' => auth()->user()->can('edit'),
            'delete' => auth()->user()->can('delete'),
        ];

        return inertia('Admin/Venue/Index', [
            'venues' => $venues,
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
            'options' => 'nullable|array',
            'options.*.name' => 'required|string|max:255',
            'options.*.with_pax' => 'boolean',
            'options.*.max_pax' => 'nullable|integer',
        ]);

        $venue = Venue::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->has('options')) {
            $options = collect($request->input('options'));

            $options->each(function ($option) use ($venue) {
                $venue->options()->create(
                    [
                        'name' => $option['name'],
                        'with_pax' => $option['with_pax'],
                        'max_pax' => $option['max_pax'],
                    ]
                );
            });

            // Remove options that are not in the request
            $optionNames = $options->pluck('name')->toArray();
            $venue->options()->whereNotIn('name', $optionNames)->delete();
        }

        return redirect()->route('admin.venues.show', $venue)->with('message', __('Venue created successfully'));
        ;
    }

    /**
     * Display the specified resource.
     */
    public function show(Venue $venue)
    {
        $venue->load('options');
        return inertia('Admin/Venue/Show', ['venue' => $venue]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venue $venue)
    {
        $venue->load('options');
        return inertia('Admin/Venue/Edit', ['venue' => $venue]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venue $venue)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'options' => 'nullable|array',
            'options.*.name' => 'required|string|max:255',
            'options.*.with_pax' => 'boolean',
            'options.*.max_pax' => 'nullable|integer',
        ]);
        $venue->update($validatedData);

        if ($request->has('options')) {
            $options = collect($request->input('options'));

            $options->each(function ($option) use ($venue) {
                $venue->options()->updateOrCreate(
                    ['name' => $option['name']],
                    [
                        'with_pax' => $option['with_pax'],
                        'max_pax' => $option['max_pax'],
                    ]
                );
            });

            // Remove options that are not in the request
            $optionNames = $options->pluck('name')->toArray();
            $venue->options()->whereNotIn('name', $optionNames)->delete();
        }

        // Redirect back to the venues index with a success message
        return redirect()->route('admin.venues.show', $venue)->with('message', __('Venue updated successfully'));
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
