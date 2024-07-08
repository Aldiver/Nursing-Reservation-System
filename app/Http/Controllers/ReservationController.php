<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Venue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Check permissions
        $canNote = $user->can('noter');
        $canApprove = $user->can('approver');

        // Fetch reservations based on permissions
        $reservations = ($canNote || $canApprove) ? Reservation::with(['user', 'noter', 'approver'])->get([
            'id',
            'date',
            'purpose',
            'remarks',
            'start_time',
            'end_time',
            'user_id',
            'noted_by',
            'approved_by',
            'isNoted',
            'isApproved'
        ]) : Reservation::with(['user', 'noter', 'approver'])
                ->where('user_id', $user->id)
                ->get([
                    'id',
                    'date',
                    'purpose',
                    'remarks',
                    'start_time',
                    'end_time',
                    'user_id',
                    'noted_by',
                    'approved_by',
                    'isNoted',
                    'isApproved'
                ]);

        // Format the reservations
        $formattedReservations = $reservations->map(function ($reservation) {
            $startTime = \Carbon\Carbon::parse($reservation->start_time);
            $endTime = \Carbon\Carbon::parse($reservation->end_time);

            $options = $reservation->options->pluck('name')->toArray();

            // Format purpose
            $purposeData = is_array($reservation->purpose) ? $reservation->purpose : json_decode($reservation->purpose, true);

            // Extract purposes and others
            $purposes = $purposeData['purpose'] ?? [];
            $others = $purposeData['others'] ?? null;

            // Combine purposes and others
            if ($others) {
                $purposes[] = "others: $others";
            }
            $combinedPurpose = implode(', ', $purposes);

            return [
                'id' => $reservation->id,
                'reserved_by' => $reservation->user->name,
                'purpose' => $combinedPurpose,
                'remarks' => $reservation->remarks,
                'options' => implode(', ', $options),
                'reservation_date' => $reservation->date . ' ' . $startTime->format('H:i') . ' - ' . $endTime->format('H:i'),
                'noted_by' => optional($reservation->noter)->name,
                'approved_by' => optional($reservation->approver)->name,
                'isNoted' => $reservation->isNoted,
                'isApproved' => $reservation->isApproved,
            ];
        });

        // Define columns
        $columns = [
            // ['label' => 'ID', 'field' => 'id'],
            ['label' => 'Reserved By', 'field' => 'reserved_by'],
            ['label' => 'Purpose', 'field' => 'purpose'],
            ['label' => 'Remarks', 'field' => 'remarks'],
            ['label' => 'Options', 'field' => 'options'],
            ['label' => 'Reservation Date', 'field' => 'reservation_date'],
            ['label' => 'Noted By', 'field' => 'noted_by', 'action' => 'note'],
            ['label' => 'Approved By', 'field' => 'approved_by', 'action' => 'approve'],
        ];

        // Define permissions
        $permissions = [
            'edit' => $user->can('edit'),
            'delete' => $user->can('delete'),
            'note' => $canNote,
            'approve' => $canApprove,
        ];

        return inertia('Reservation/Index', [
            'reservations' => $formattedReservations,
            'columns' => $columns,
            'permissions' => $permissions,
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
        // Define the validation rules
        $rules = [
            'date' => 'required|date',
            'start_time' => 'required|date_format:h:i A',
            'end_time' => 'required|date_format:h:i A|after:start_time',
            'purpose' => 'nullable|array',
            'purpose.*' => 'integer',
            'otherPurpose' => 'nullable|string',
            'options' => 'required|array',
            'options.*' => 'integer|exists:options,id',
            'pax' => 'nullable|array',
            'pax.*' => 'nullable|integer',
        ];

        // Add conditional validation rules for purposeType and materials if purpose contains 2
        if (in_array(2, $request->input('purpose', []))) {
            $rules['purposeType'] = 'required|string';
            $rules['materials'] = 'required|string';
        } else {
            $rules['purposeType'] = 'nullable|string';
            $rules['materials'] = 'nullable|string';
        }

        // Validate the incoming request
        $validatedData = $request->validate($rules);

        // Convert purpose indices to corresponding strings
        $purposeMapping = [
            0 => 'Class',
            1 => 'Meeting',
            2 => 'Demonstration/Return Demonstration',
        ];
        $purposes = array_map(function ($purpose) use ($purposeMapping) {
            return $purposeMapping[$purpose] ?? $purpose;
        }, $validatedData['purpose']);

        $otherPurpose = $validatedData['otherPurpose'] ?? null;

        $toStore = [
            'purpose' => $purposes,
            'others' => $otherPurpose
        ];
        // Concatenate purposeType and materials for remarks
        $remarks = isset($validatedData['purposeType'], $validatedData['materials'])
            ? 'Purpose Type: ' . $validatedData['purposeType'] . "\n\n" . 'Materials Needed:' .$validatedData['materials']
            : '';

        // Convert times to 24-hour format
        $start_time = Carbon::createFromFormat('h:i A', $request->start_time)->format('H:i');
        $end_time = Carbon::createFromFormat('h:i A', $request->end_time)->format('H:i');

        // Validate date constraints for reservation times
        $date = new \DateTime($request->date);
        if ($date->format('N') >= 6 && !($date->format('N') == 6 && $request->start_time >= '08:00' && $request->end_time <= '12:00')) {
            return back()->withErrors(['date' => 'Reservations are only allowed on weekdays from 7 AM to 4 PM, and Saturdays from 8 AM to 12 PM.']);
        }

        // Find users with specific roles and permissions
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

        // Create reservation
        $reservation = Reservation::create([
            'date' => $date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'isNoted' => false,
            'isApproved' => false,
            'user_id' => auth()->id(),
            'noted_by' => $noter?->id,
            'approved_by' => $approver?->id,
            'purpose' => $toStore,
            'remarks' => $remarks,
        ]);

        // Attach options with pax to the reservation
        foreach ($validatedData['options'] as $optionId) {
            $reservation->options()->attach($optionId, ['pax' => $validatedData['pax'][$optionId] ?? null]);
        }

        return redirect()->route('reservations.index');
    }

    public function show(Reservation $reservation)
    {
        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        // Check if the reservation is approved and if the authenticated user has permission to edit
        if ($reservation->isApproved && Gate::denies('approver')) {
            return redirect()->back()
                             ->with('message', __('Only approver can edit this Reservation. Contact administrator for details.'));
        }

        // Load venues with their options to populate the edit form
        $venues = Venue::with('options')->get();
        $reservation->load('options');

        // Return the inertia view for editing the reservation with the reservation and venues data
        return inertia('Reservation/Edit', [
            'reservation' => $reservation,
            'venues' => $venues,
        ]);
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
        return redirect()->route('reservations.index')->with('message', __('Reservation deleted successfully'));

    }

    public function note(Reservation $reservation)
    {
        $this->authorize('noter', $reservation);
        $reservation->update(['isNoted' => !$reservation->isNoted]);
        $reservation->update(['noted_by' => auth()->id()]);
        return redirect()->route('reservations.index');
    }

    public function approve(Reservation $reservation)
    {
        $this->authorize('approver', $reservation);
        $reservation->update(['isApproved' => !$reservation->isApproved]);
        $reservation->update(['approved_by' => auth()->id()]);
        return redirect()->route('reservations.index');
    }

    public function getUnavailableOptions(Request $request)
    {
        $date = $request->input('date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        // Retrieve reservations based on date and approved status
        $query = Reservation::query()
            ->whereDate('date', $date)
            ->where('isApproved', true); // Ensure only approved reservations are considered

        // If both start and end times are provided, filter for overlapping reservations
        if ($startTime && $endTime) {
            $query->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '>=', $startTime)
                        ->where('start_time', '<', $endTime);
                })->orWhere(function ($query) use ($startTime, $endTime) {
                    $query->where('end_time', '>', $startTime)
                        ->where('end_time', '<=', $endTime);
                });
            });
        }

        // Fetch the reservations
        $reservations = $query->get();

        // Prepare unavailable options from approved reservations
        $unavailableOptions = [];
        foreach ($reservations as $reservation) {
            foreach ($reservation->options as $option) {
                $unavailableOptions[] = $option->id;
            }
        }

        return response()->json(['unavailableOptions' => $unavailableOptions]);
    }
}
