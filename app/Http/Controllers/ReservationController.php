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

        // Format the reservations and check for conflicts
        $formattedReservations = $reservations->map(function ($reservation) {
            $startTime = \Carbon\Carbon::parse($reservation->start_time);
            $endTime = \Carbon\Carbon::parse($reservation->end_time);

            $options = $reservation->options->sortBy('id')->pluck('name')->toArray();

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

            // Check for overlapping reservations
            $allOptions = $reservation->options->pluck('name', 'id')->toArray();
            // dd($options);
            $unavailableOptions = $this->getUnavailableOptionsForTimeRange($reservation->date, $reservation->start_time, $reservation->end_time, $reservation->id);

            $conflictingOptions = array_filter($allOptions, function ($key) use ($unavailableOptions) {
                return in_array($key, $unavailableOptions);
            }, ARRAY_FILTER_USE_KEY);

            $conflictingOptions = array_values($conflictingOptions);
            $conflict = !empty($conflictingOptions);

            // dd($conflictingOptions);

            return [
                'id' => $reservation->id,
                'reserved_by' => $reservation->user->name,
                'purpose' => $combinedPurpose,
                // 'remarks' => $reservation->remarks,
                'options' => $options,
                // 'options' => implode(', ', $options),
                'reservation_date' => $reservation->date . ' ' . $startTime->format('H:i') . ' - ' . $endTime->format('H:i'),
                'noted_by' => optional($reservation->noter)->name,
                'approved_by' => optional($reservation->approver)->name,
                'isNoted' => $reservation->isNoted,
                'isApproved' => $reservation->isApproved,
                'conflict' => $conflict,
                'conflictData' => implode(', ', $conflictingOptions),
            ];
        });

        // Define columns
        $columns = [
            // ['label' => 'ID', 'field' => 'id'],
            ['label' => 'Reserved By', 'field' => 'reserved_by'],
            ['label' => 'Purpose', 'field' => 'purpose'],
            // ['label' => 'Remarks', 'field' => 'remarks'],
            ['label' => 'Options', 'field' => 'options'],
            ['label' => 'Reservation Date', 'field' => 'reservation_date'],
            ['label' => 'Noted By', 'field' => 'noted_by', 'action' => 'note', 'isConflict' => 'conflict', 'conflict_data' => 'conflicData'],
            ['label' => 'Approved By', 'field' => 'approved_by', 'action' => 'approve', 'isConflict' => 'conflict', 'conflict_data' => 'conflicData'],
        ];

        // Define permissions
        $permissions = [
            'show' => $user->can("show"),
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
            // 'options.*' => 'integer|exists:options,id',
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
        $optionsWithPax = collect($validatedData['options'])->mapWithKeys(function ($option) use ($validatedData) {
            return [
                $option['id'] => ['pax' => $validatedData['pax'][$option['id']] ?? null]
            ];

        })->toArray();

        // Attach the options with the reservation
        $reservation->options()->attach($optionsWithPax);

        return redirect()->route('reservations.index')->with('message', __("Reservation with id {$reservation->id} created successfully"));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load('user', 'noter', 'approver');
        return inertia('Reservation/Show', [
            'reservation' => $reservation,
        ]);
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
        $reservation->with('options');

        $allOptions = $reservation->options->pluck('name', 'id', 'pax')->toArray();
        $unavailableOptions = $this->getUnavailableOptionsForTimeRange($reservation->date, $reservation->start_time, $reservation->end_time, $reservation->id);
        $conflictingOptions = array_filter($allOptions, function ($key) use ($unavailableOptions) {
            return in_array($key, $unavailableOptions);
        }, ARRAY_FILTER_USE_KEY);

        $conflictingOptions = array_values($conflictingOptions);
        if($conflictingOptions) {
            session()->flash('error', __(implode(', ', array_values($conflictingOptions)) . ' removed from selection list. (Conflicting schedules)'));
        }
        return inertia('Reservation/Edit', [
            'reservation' => $reservation,
            'venues' => $venues,
        ]);
    }


    public function update(Request $request, Reservation $reservation)
    {
        $rules = [
            'date' => 'required|date',
            'start_time' => 'required|date_format:h:i A',
            'end_time' => 'required|date_format:h:i A|after:start_time',
            'purpose' => 'nullable|array',
            'purpose.*' => 'integer',
            'otherPurpose' => 'nullable|string',
            'options' => 'required|array',
            // 'options.*' => 'integer|exists:options,id',
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

        // Prepare the array of option IDs with their corresponding pax values
        $optionsWithPax = collect($validatedData['options'])->mapWithKeys(function ($option) use ($validatedData) {
            // Check if the option has with_pax set to true
            if ($option['with_pax']) {
                return [
                    $option['id'] => ['pax' => $validatedData['pax'][$option['id']] ?? null]
                ];
            } else {
                return [
                    $option['id'] => []
                ];
            }
        })->toArray();

        // Sync the options with the reservation
        $reservation->options()->sync($optionsWithPax);

        $reservation->update([
            'date' => $date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'purpose' => $toStore,
            'remarks' => $remarks,
        ]);
        return redirect()->route('reservations.index')->with('message', __("Reservation with id {$reservation->id} updated successfully"));
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
        $currentReservationId = $request->input('id');

        $unavailableOptions = $this->getUnavailableOptionsForTimeRange($date, $startTime, $endTime, $currentReservationId);

        return response()->json(['unavailableOptions' => $unavailableOptions]);
    }

    private function getUnavailableOptionsForTimeRange($date, $startTime, $endTime, $currentReservationId = null)
    {
        $checkOverlap = function ($a, $b, $c, $d) {
            return max($a, $c) < min($b, $d);
        };

        $query = Reservation::query()
            ->where('isApproved', true)
            ->whereDate('date', $date);

        if ($startTime && $endTime) {
            $query->where(function ($query) use ($startTime, $endTime, $checkOverlap) {
                $checkOverlap('start_time', 'end_time', $startTime, $endTime);
            });
        }

        // Exclude the current reservation
        if ($currentReservationId) {
            $query->where('id', '!=', $currentReservationId);
        }

        $reservations = $query->get();
        $unavailableOptions = [];
        foreach ($reservations as $reservation) {
            foreach ($reservation->options as $option) {
                $unavailableOptions[] = $option->id;
            }
        }

        return $unavailableOptions;
    }
}
