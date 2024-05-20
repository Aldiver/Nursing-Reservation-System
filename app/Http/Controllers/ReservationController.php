<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Venue;
use App\Models\User;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['venue', 'notedBy', 'approvedBy'])->get();
        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        $venues = Venue::all();
        $noters = User::role('noter')->get();
        $approvers = User::role('approver')->get();
        return view('reservations.create', compact('venues', 'noters', 'approvers'));
    }

    public function store(Request $request)
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
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })->first();

        if ($existingReservation) {
            return redirect()->back()->withErrors(['time' => 'The selected time slot is already booked.']);
        }

        Reservation::create($request->all());
        return redirect()->route('reservations.index');
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
