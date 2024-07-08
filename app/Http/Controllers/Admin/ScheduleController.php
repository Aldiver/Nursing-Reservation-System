<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Fetch only approved reservations
        $reservations = Reservation::with(['user'])
            ->where('isApproved', true)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get([
                'id', 'date', 'start_time', 'end_time', 'user_id', 'isApproved'
            ]);

        $formattedReservations = $reservations->map(function ($reservation) {
            $date = \Carbon\Carbon::parse($reservation->date)->format('F j, Y');
            $startTime = \Carbon\Carbon::parse($reservation->start_time)->format('g:i A');
            $endTime = \Carbon\Carbon::parse($reservation->end_time)->format('g:i A');

            return [
                'id' => $reservation->id,
                'reserved_by' => $reservation->user->name,
                'date' => $date,
                'time' => "$startTime - $endTime",
            ];
        });

        // Define columns
        $columns = [
            ['label' => 'Reserved By', 'field' => 'reserved_by'],
            ['label' => 'Date', 'field' => 'date'],
            ['label' => 'Time', 'field' => 'time'],
        ];

        // Define permissions
        $permissions = [
            'edit' => $user->can('edit'),
            'delete' => $user->can('delete'),
        ];

        return inertia('Admin/Schedule/Index', [
            'reservations' => $formattedReservations,
            'columns' => $columns,
            'permissions' => $permissions,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
