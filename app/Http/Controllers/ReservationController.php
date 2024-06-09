<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Venue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['user', 'options', 'noter', 'approver'])->get();
        $venues = Venue::All();
        return inertia('Reservation/Index', [
            'reservations' => $reservations,
            'venues' => $venues,
        ]);
    }

    public function create()
    {
        // Eager load options for each venue
        $venues = Venue::with('options')->get();

        return inertia('Reservation/Create', [
            'venues' => $venues,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:h:i A',
            'end_time' => 'required|date_format:h:i A|after:start_time',
            'purpose' => 'nullable|array', // Ensure it's an array
            'purpose.*' => 'string', // Ensure each item in the array is a string
        ]);


        $start_time = Carbon::createFromFormat('h:i A', $request->start_time)->format('H:i');
        $end_time = Carbon::createFromFormat('h:i A', $request->end_time)->format('H:i');

        // Convert date string to DateTime object
        $date = new \DateTime($request->date);

        // Check the constraints for date and time
        if ($date->format('N') >= 6 && !($date->format('N') == 6 && $request->start_time >= '08:00' && $request->end_time <= '12:00')) {
            return back()->withErrors(['date' => 'Reservations are only allowed on weekdays from 7 AM to 4 PM, and Saturdays from 8 AM to 12 PM.']);
        }

        $noter = User::whereHas('roles', function ($query) {
            $query->where('name', 'Staff');
        })->whereHas('permissions', function ($query) {
            $query->where('name', 'noter');
        })->first();

        $approver = User::whereHas('roles', function ($query) {
            $query->where('name', 'Staff');
        })->whereHas('permissions', function ($query) {
            $query->where('name', 'approver');
        })->first();

        $reservation = Reservation::create([
            'date' => $date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'isNoted' => false,
            'isApproved' => false,
            'user_id' => auth()->id(),
            'noted_by' => $noter ? $noter->id : null,
            'approved_by' => $approver ? $approver->id : null,
            'purpose' => $request->purpose,
        ]);

        $reservation->options()->attach($request->options);

        // Add notification logic here

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
