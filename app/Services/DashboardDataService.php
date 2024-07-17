<?php

// app/Services/DashboardDataService.php

namespace App\Services;

use App\Models\DashboardData;
use App\Models\Reservation;
use App\Models\Option;
use Carbon\Carbon;

class DashboardDataService
{
    public function updateDashboardData()
    {
        $startDate = Carbon::now()->startOfWeek();
        $dashboardData = DashboardData::where('week_start_date', $startDate)->firstOrCreate(
            ['week_start_date' => $startDate],
            [
                'pending_approvals_count' => 0,
                'recent_reservations_count' => 0,
                'venue_utilization_rate' => 0.00,
            ]
        );

        $this->updatePendingApprovalsCount($dashboardData);
        $this->updateRecentReservationsCount($dashboardData);
        $this->updateVenueUtilizationRate($dashboardData);

        return $dashboardData->refresh();
    }

    private function updatePendingApprovalsCount(DashboardData $dashboardData)
    {
        $count = Reservation::where('status', 'Pending')->count();
        $dashboardData->update(['pending_approvals_count' => $count]);
    }

    private function updateRecentReservationsCount(DashboardData $dashboardData)
    {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();

        $reservations = Reservation::where('status', 'Approved')->get();

        $count = $reservations->filter(function ($reservation) use ($startDate, $endDate) {
            $reservationDate = Carbon::parse($reservation->date);
            return $reservationDate->between($startDate, $endDate);
        })->count();

        // echo "Dashboard Data: ", json_encode($dashboardData), PHP_EOL;
        // echo "Total count: $count\nStart Date: $startDate\nEnd Date: $endDate\n";

        $dashboardData->update(['recent_reservations_count' => $count]);
    }

    // private function updateVenueUtilizationRate(DashboardData $dashboardData)
    // {
    //     $startDate = Carbon::now()->startOfWeek();
    //     $endDate = $startDate->copy()->endOfWeek();

    //     $totalOptions = Option::count();
    //     $daysOfOperation = 6;  // Assuming Monday to Saturday
    //     $totalSlots = $totalOptions * $daysOfOperation;
    //     $utilizedSlots = 0;

    //     $approvedReservations = Reservation::where('status', 'Approved')
    //     ->get();

    //     $approvedReservations = $approvedReservations->filter(function ($reservation) use ($startDate, $endDate) {
    //         $reservationDate = Carbon::parse($reservation->date);
    //         return $reservationDate->between($startDate, $endDate);
    //     });

    //     $reservationsGroupedByOption = $approvedReservations->groupBy('option_id');

    //     foreach ($reservationsGroupedByOption as $optionId => $reservations) {
    //         $reservationsGroupedByDay = $reservations->groupBy(function ($reservation) {
    //             return Carbon::parse($reservation->date)->toDateString();
    //         });

    //         $daysWithReservations = $reservationsGroupedByDay->count();
    //         $utilizedSlots += $daysWithReservations;
    //     }

    //     $utilizationRate = ($utilizedSlots / $totalSlots) * 100;
    //     $dashboardData->update(['venue_utilization_rate' => $utilizationRate]);

    //     // echo "Dashboard Data: ", json_encode($approvedReservations), PHP_EOL;
    //     // echo "Utilized Slots: $utilizedSlots\nTotal Slots: $totalSlots\nUtilization Rate: $utilizationRate\n";
    // }

    private function updateVenueUtilizationRate(DashboardData $dashboardData)
    {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();

        $totalOptions = Option::count();
        $daysOfOperation = 6;  // Assuming Monday to Saturday
        $totalSlots = $totalOptions * $daysOfOperation;
        $utilizedSlots = 0;

        // Get approved reservations for the current week
        $approvedReservations = Reservation::where('status', 'Approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->with('options') // Eager load options relationship
            ->get();

        // Initialize an array to store utilized slots per day
        $utilizedSlotsPerDay = [];

        // Group reservations by date
        $reservationsGroupedByDay = $approvedReservations->groupBy(function ($reservation) {
            return Carbon::parse($reservation->date)->toDateString();
        });

        foreach ($reservationsGroupedByDay as $date => $reservations) {
            // Initialize an array to store unique options for the current day
            $uniqueOptions = [];

            // Collect unique options for the current day
            foreach ($reservations as $reservation) {
                foreach ($reservation->options as $option) {
                    if (!in_array($option->id, $uniqueOptions)) {
                        $uniqueOptions[] = $option->id;
                    }
                }
            }

            // Count the number of unique options for the current day
            $utilizedSlotsPerDay[$date] = count($uniqueOptions);
        }

        // Sum up utilized slots from all days
        $utilizedSlots = array_sum($utilizedSlotsPerDay);

        // Calculate the utilization rate
        $utilizationRate = ($utilizedSlots / $totalSlots) * 100;
        $dashboardData->update(['venue_utilization_rate' => $utilizationRate]);

        // echo "Approved Reservation Count: " . $approvedReservations->count() . "\n";
        // echo "Utilized Slots: $utilizedSlots\nTotal Slots: $totalSlots\nUtilization Rate: $utilizationRate\n";
    }
}
