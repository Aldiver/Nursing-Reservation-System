<?php

namespace App\Http\Controllers;

use App\Events\ReservationEvent;
use App\Models\Reservation;
use App\Models\Venue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use App\Constants\NotificationTypes;
use Barryvdh\DomPDF\Facade\Pdf;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Reservation::class, 'reservation');
    }
    public function index()
    {
        $user = auth()->user();

        // Check permissions
        $canNote = $user->can('noter');
        $canApprove = $user->can('approver');

        // Get filters from the request
        $statusFilter = request()->input('status', []);
        $filterByDate = request()->input('filterByDate');
        $sortByTimeStart = request()->input('sortByTimeStart');
        // dd($filterByDate);

        // Fetch reservations based on permissions and apply filters
        $query = Reservation::with(['user', 'noter', 'approver', 'options']);
        if ($statusFilter) {
            $query->whereIn('status', $statusFilter);
            // dd($query);
        }

        if ($filterByDate) {
            $date = new \DateTime($filterByDate);
            $query->whereDate('date', $filterByDate);
        }

        if ($sortByTimeStart === "true") {
            $query->orderBy('start_time', 'asc');
        } else {
            $query->orderBy('start_time', 'desc');
        }

        // Fetch reservations based on permissions
        $reservations = ($canNote || $canApprove) ? $query->get([
            'id',
            'date',
            'purpose',
            'remarks',
            'start_time',
            'end_time',
            'user_id',
            'status',
            'noted_by',
            'approved_by',
            'isNoted',
            'isApproved'
        ]) : $query->where('user_id', $user->id)->get([
            'id',
            'date',
            'purpose',
            'remarks',
            'start_time',
            'end_time',
            'user_id',
            'status',
            'noted_by',
            'approved_by',
            'isNoted',
            'isApproved'
        ]);

        // Format the reservations and check for conflicts
        $formattedReservations = $reservations->map(function ($reservation) {
            $startTime = \Carbon\Carbon::parse($reservation->start_time);
            $endTime = \Carbon\Carbon::parse($reservation->end_time);

            $options = $reservation->options->sortBy('id')->map(function ($option) {
                return $option->pivot->pax ? "{$option->name} - ({$option->pivot->pax} pax)" : $option->name;
            })->values()->toArray();

            $purposeData = is_array($reservation->purpose) ? $reservation->purpose : json_decode($reservation->purpose, true);
            $purposes = $purposeData['purpose'] ?? [];
            $others = $purposeData['others'] ?? null;
            if ($others) {
                $purposes[] = "others: $others";
            }
            $combinedPurpose = implode(', ', $purposes);
            $conflict = false;
            $conflictingOptions = [];

            if (!$reservation->isApproved) {
                $allOptions = $reservation->options->pluck('name', 'id')->toArray();
                $unavailableOptions = $this->getUnavailableOptionsForTimeRange($reservation->date, $reservation->start_time, $reservation->end_time, $reservation->id);
                $conflictingOptions = array_filter($allOptions, function ($key) use ($unavailableOptions) {
                    return in_array($key, $unavailableOptions['unavailableOptions']);
                }, ARRAY_FILTER_USE_KEY);
                $conflictingOptions = array_values($conflictingOptions);
                $conflict = !empty($conflictingOptions);

                if ($conflict) {
                    $owner = User::find($reservation->user_id);
                    $existingNotification = $owner->notifications()
                        ->where('data->reservation', $reservation->id)
                        ->where('data->notif_type', 'conflict')
                        ->first();
                    // dd($existingNotification);
                    if (!$existingNotification) {
                        event(new ReservationEvent($owner, 0, $reservation->id, NotificationTypes::conflict));
                    }
                }
            }

            return [
                'id' => $reservation->id,
                'reserved_by' => $reservation->user->name,
                'purpose' => $combinedPurpose,
                'options' => $options,
                'reservation_date' => $reservation->date . ' ' . $startTime->format('H:i') . ' - ' . $endTime->format('H:i'),
                'status' => $reservation->status,
                'noted_by' => optional($reservation->noter)->name,
                'approved_by' => optional($reservation->approver)->name,
                'isNoted' => $reservation->isNoted,
                'isApproved' => $reservation->isApproved,
                'conflict' => $conflict,
                'conflictData' => implode(', ', $conflictingOptions),
            ];
        });

        $columns = [
            ['label' => 'ID', 'field' => 'id'],
            ['label' => 'Reserved By', 'field' => 'reserved_by'],
            ['label' => 'Purpose', 'field' => 'purpose'],
            ['label' => 'Options', 'field' => 'options'],
            ['label' => 'Reservation Date', 'field' => 'reservation_date'],
            ['label' => 'Noted By', 'field' => 'noted_by', 'action' => 'note', 'isConflict' => 'conflict', 'conflict_data' => 'conflictData'],
            ['label' => 'Approved By', 'field' => 'approved_by', 'action' => 'approve', 'isConflict' => 'conflict', 'conflict_data' => 'conflictData'],
            ['label' => 'Status', 'field' => 'status'],
        ];

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
            'sotr' => $sortByTimeStart
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
            'purpose' => 'required|array',
            'purpose.*' => 'integer',
            'otherPurpose' => 'nullable|string',
            'options' => 'required|array',
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
            ? 'Purpose Type: ' . $validatedData['purposeType'] . ",\n\n" . 'Materials Needed:' . $validatedData['materials']
            : '';

        // Convert times to 24-hour format
        $start_time = Carbon::createFromFormat('h:i A', $request->start_time)->format('H:i');
        $end_time = Carbon::createFromFormat('h:i A', $request->end_time)->format('H:i');

        // Validate date constraints for reservation times
        $date = new \DateTime($request->date);

        // Check if it's a weekend (Saturday or Sunday)
        if ($date->format('N') >= 6) {
            // If it's Saturday (6) or Sunday (7)
            if (!($date->format('N') == 6 && $start_time >= '08:00' && $end_time <= '12:00')) {
                // Only allow reservations on Saturdays from 8 AM to 12 PM
                return back()->withErrors(['date' => 'Reservations are only allowed on Saturdays from 8 AM to 12 PM.']);
            }
        } else {
            // If it's a weekday (Monday to Friday)
            if (!($start_time >= '07:00' && $end_time <= '16:00')) {
                // Only allow reservations on weekdays from 7 AM to 4 PM
                return back()->withErrors(['date' => 'Reservations are only allowed on weekdays from 7 AM to 4 PM.']);
            }
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
            'status' => "Pending"
        ]);

        // Attach options with pax to the reservation
        $optionsWithPax = collect($validatedData['options'])->mapWithKeys(function ($option) use ($validatedData) {
            return [
                $option['id'] => ['pax' => $validatedData['pax'][$option['id']] ?? null]
            ];
        })->toArray();

        // Attach the options with the reservation
        $reservation->options()->attach($optionsWithPax);

        // Trigger the ReservationEvent for each user
        $auth_user = auth()->user()->name;

        $noterUsers = User::permission('noter')->get();

        foreach ($noterUsers as $user) {
            event(new ReservationEvent($user, $auth_user, $reservation->id, NotificationTypes::created));
        }

        return redirect()->route('reservations.index')->with('message', __("Reservation with id {$reservation->id} created successfully"));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load('user', 'noter', 'approver', 'options');
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
            return in_array($key, $unavailableOptions['unavailableOptions']);
        }, ARRAY_FILTER_USE_KEY);

        $conflictingOptions = array_values($conflictingOptions);


        //display conflict warning in Edit Page
        if ($conflictingOptions) {
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
            'purpose' => 'required|array',
            'otherPurpose' => 'nullable|string',
            'options' => 'required|array',
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
            ? 'Purpose Type: ' . $validatedData['purposeType'] . ",\n\n" . 'Materials Needed:' . $validatedData['materials']
            : '';

        // Convert times to 24-hour format
        $start_time = Carbon::createFromFormat('h:i A', $request->start_time)->format('H:i');
        $end_time = Carbon::createFromFormat('h:i A', $request->end_time)->format('H:i');

        // Validate date constraints for reservation times
        $date = new \DateTime($request->date);

        // Check if it's a weekend (Saturday or Sunday)
        if ($date->format('N') >= 6) {
            // If it's Saturday (6) or Sunday (7)
            if (!($date->format('N') == 6 && $start_time >= '08:00' && $end_time <= '12:00')) {
                // Only allow reservations on Saturdays from 8 AM to 12 PM
                return back()->withErrors(['date' => 'Reservations are only allowed on Saturdays from 8 AM to 12 PM.']);
            }
        } else {
            // If it's a weekday (Monday to Friday)
            if (!($request->start_time >= '07:00' && $request->end_time <= '16:00')) {
                // Only allow reservations on weekdays from 7 AM to 4 PM
                return back()->withErrors(['date' => 'Reservations are only allowed on weekdays from 7 AM to 4 PM.']);
            }
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

        // Trigger the ReservationEvent for each user
        $auth_user = auth()->user();
        $owner = User::find($reservation->user_id);

        if ($auth_user->id != $reservation->user_id) {
            event(new ReservationEvent($auth_user, $owner->name, $reservation->id, NotificationTypes::updated));
        }

        //updates reservation to the database
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
        $auth_user = auth()->user();
        $owner = User::find($reservation->user_id);

        $reservation->delete();

        if ($auth_user->id != $reservation->user_id) {
            event(new ReservationEvent($auth_user, $owner->name, $reservation->id, NotificationTypes::deleted));
        }

        return redirect()->route('reservations.index')->with('message', __('Reservation deleted successfully'));
    }

    public function note(Reservation $reservation)
    {
        // Authorize the action
        $this->authorize('noter', $reservation);

        // Check if the reservation is already noted and redirect if true
        if ($reservation->isNoted && $reservation->noted_by != null) {
            return redirect()->route('reservations.index')->with('message', __("Reservation with id {$reservation->id} already noted"));
        }

        // Update reservation's noted status and noted_by fields
        $reservation->update([
            'isNoted' => true,
            'noted_by' => auth()->id()
        ]);

        $authUser = auth()->user();
        $owner = $reservation->user;

        // Trigger event for the owner if they are not the one noting the reservation
        if ($owner->id != $authUser->id) {
            event(new ReservationEvent($owner, $authUser->name, $reservation->id, NotificationTypes::notify_noted));
        }

        // Trigger event for all users with the 'approver' permission
        User::permission('approver')->each(function ($user) use ($authUser, $reservation) {
            event(new ReservationEvent($user, $authUser->name, $reservation->id, NotificationTypes::noted));
        });

        return redirect()->route('reservations.index');
    }

    public function reject_noter(Reservation $reservation)
    {
        // Authorize the action
        $this->authorize('noter', $reservation);

        // Check if the reservation is already noted and redirect if true
        if ($reservation->isNoted && $reservation->noted_by != null) {
            return redirect()->route('reservations.index')->with('message', __("Reservation with id {$reservation->id} already noted"));
        }

        // Update reservation's noted status and noted_by fields
        $reservation->update([
            'isNoted' => false,
            'status' => "Rejected",
        ]);

        $authUser = auth()->user();
        $owner = $reservation->user;

        // Trigger event for the owner if they are not the one noting the reservation
        if ($owner->id != $authUser->id) {
            event(new ReservationEvent($owner, $authUser->name, $reservation->id, NotificationTypes::notify_noted));
        }

        return redirect()->route('reservations.index');
    }


    public function approve(Reservation $reservation)
    {
        $this->authorize('approver', $reservation);
        $reservation->update([
            'isApproved' => true,
            'approved_by' => auth()->id(),
            'status' => 'Approved'
        ]);

        $auth_user = auth()->user();
        $owner = User::find($reservation->user_id);

        event(new ReservationEvent($owner, $auth_user->name, $reservation->id, NotificationTypes::approved));

        return redirect()->route('reservations.index');
    }

    public function reject_approver(Reservation $reservation)
    {
        $this->authorize('approver', $reservation);
        $reservation->update([
            'isNoted' => false,
            'noted_by' => null,
            'approved_by' => null,
            'status' => 'Rejected'
        ]);

        $auth_user = auth()->user();
        $owner = User::find($reservation->user_id);

        // event(new ReservationEvent($owner, $auth_user->name, $reservation->id, NotificationTypes::approved));

        return redirect()->route('reservations.index');
    }

    public function getUnavailableOptions(Request $request)
    {
        $startTime = null;
        $endTime = null;
        $date = null;
        if ($request->start_time) {
            $startTime = Carbon::createFromFormat('h:i A', $request->start_time)->format('H:i');
        }
        if ($request->end_time) {
            $endTime = Carbon::createFromFormat('h:i A', $request->end_time)->format('H:i');
        }
        if ($request->date) {
            $date = new \DateTime($request->date);
        }

        $currentReservationId = $request->input('id');

        $unavailableOptions = $this->getUnavailableOptionsForTimeRange($date, $startTime, $endTime, $currentReservationId);

        return response()->json(['unavailableOptions' => $unavailableOptions]);
    }

    private function getUnavailableOptionsForTimeRange($date, $startTime, $endTime, $currentReservationId = null)
    {
        $query = Reservation::query()->where('isNoted', true)
            ->whereDate('date', $date);

        if ($date && $startTime && $endTime) {
            $query->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($subquery) use ($startTime, $endTime) {
                    $subquery->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
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
        // return $unavailableOptions;
        return ["unavailableOptions" => $unavailableOptions, "conflictReservation" => $reservations];
    }

    public function print(Reservation $reservation)
    {
        $pdf = Pdf::loadView('pdf.invoice', $reservation->toArray());
        return $pdf->stream('invoice.pdf');
    }

    public function preview(Reservation $reservation)
    {
        // Check if the status is 'Approved'
        if ($reservation->status !== 'Approved') {
            return redirect()->back()->with('error', 'Reservation is not approved.');
        }
        
        $reservation->load('user', 'noter', 'approver', 'options');

        // Retrieve purpose data
        $purposeData = $reservation->purpose;

        // Extract data
        $data1 = $purposeData['purpose'] ?? [];
        $data2 = $purposeData['others'] ?? null;

        // Convert array to comma-separated string
        $purposeString = implode(', ', $data1);

        // Append 'Others: ' if $data2 is not null
        if ($data2) {
            $purposeString .= ', Others: ' . $data2;
        }
        // dd($reservation);
        // Prepare data for the PDF
        $data = [
            'id' => $reservation->id,
            'reserved_by' => $reservation->user->name,
            'schedule' => "{$reservation->date} - {$reservation->start_time} - {$reservation->end_time}",
            'options' => $reservation->options->map(function ($option) {
                return $option->with_pax
                    ? "{$option->name} ({$option->pivot->pax} pax)"
                    : $option->name;
            })->implode(', '),
            'purpose' => $purposeString,
            'remarks' => $reservation->remarks,
            'status' => $reservation->status,
            'noted_by' => $reservation->noter->name,
            'noter_pos' => $reservation->noter->position,
            'approved_by' => $reservation->approver->name,
            'approver_pos' => $reservation->approver->position,
        ];

        $pdf = Pdf::loadView('pdf-preview', $data);
        $pdfContent = $pdf->setPaper('a4', 'portrait')->stream(); // Get the PDF content

        return inertia('Reservation/Pdf', [
            'pdfContent' => base64_encode($pdfContent), // Encode content for embedding
        ]);
    }
}
