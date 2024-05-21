<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Venue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['user', 'venue', 'noter', 'approver'])->get();
        return inertia('Reservation/Index', [
            'reservations' => $reservations,
        ]);
    }

    public function create()
    {
        $venues = Venue::all();
        $noters = User::permission('noter')->get();
        $approvers = User::permission('approver')->get();

        return inertia('Reservation/Create', [
            'venues' => $venues,
            'noters' => $noters,
            'approvers' => $approvers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'number_of_participants' => 'nullable|integer',
            'venue_id' => 'required|exists:venues,id',
            'noted_by' => 'nullable|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
        ]);

        // Check the constraints for date and time
        if ($request->date->isWeekend() && !($request->date->isSaturday() && $request->start_time >= '08:00' && $request->end_time <= '12:00')) {
            return back()->withErrors(['date' => 'Reservations are only allowed on weekdays from 7 AM to 4 PM, and Saturdays from 8 AM to 12 PM.']);
        }

        $reservation = Reservation::create([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'number_of_participants' => $request->number_of_participants,
            'isNoted' => false,
            'isApproved' => false,
            'user_id' => Auth::id(),
            'venue_id' => $request->venue_id,
            'noted_by' => $request->noted_by,
            'approved_by' => $request->approved_by,
        ]);

        // Add notification logic here

        return redirect()->route('admin.reservations.index');
    }

    public function show(Reservation $reservation)
    {
        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        $venues = Venue::all();
        $noters = User::role('noter')->get();
        $approvers = User::role('approver')->get();
        return view('reservations.edit', compact('reservation', 'venues', 'noters', 'approvers'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $request->validate([
            'venue_id' => 'required',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'number_of_participants' => 'required|integer',
            'noted_by' => 'required|exists:users,id',
            'approved_by' => 'required|exists:users,id',
        ]);

        $existingReservation = Reservation::where('venue_id', $request->venue_id)
            ->where('date', $request->date)
            ->where('id', '!=', $reservation->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })->first();

        if ($existingReservation) {
            return redirect()->back()->withErrors(['time' => 'The selected time slot is already booked.']);
        }

        $reservation->update($request->all());
        return redirect()->route('reservations.index');
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return redirect()->route('reservations.index');
    }

    public function note(Reservation $reservation)
    {
        $this->authorize('noter', $reservation);
        $reservation->update(['isNoted' => !$reservation->isNoted]);
        return redirect()->route('reservations.index');
    }

    public function approve(Reservation $reservation)
    {
        $this->authorize('approver', $reservation);
        $reservation->update(['isApproved' => !$reservation->isApproved]);
        return redirect()->route('reservations.index');
    }
}
