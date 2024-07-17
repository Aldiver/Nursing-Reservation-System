<?php

namespace App\Http\Controllers;

use App\Models\DashboardData;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Carbon\Carbon;
use App\Models\Trend;
use App\Models\Option;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $user = auth()->user();
        $role = $user->role; // Assuming the role is stored in a `role` attribute

        if ($role === 'Staff') {
            $reservations = Reservation::with(['user', 'noter', 'approver', 'options'])
                ->where('user_id', $user->id)
                ->where('isApproved', true)
                ->get();

            // Return only the reservations related to the user
            return inertia('Dashboard/Index', [
                'reservations' => $reservations,
            ]);
        }

        $currentWeek = Carbon::now()->startOfWeek();
        $previousWeek = Carbon::now()->startOfWeek()->subWeek();

        $currentWeekData = DashboardData::where('week_start_date', $currentWeek->toDateString())->first();
        $previousWeekData = DashboardData::where('week_start_date', $previousWeek->toDateString())->first();

        $pendingApprovalCount = $currentWeekData->pending_approvals_count;

        $recentReservationsCount = $currentWeekData->recent_reservations_count;

        $utilizationRate = $currentWeekData->venue_utilization_rate;

        $pendingApprovalTrend = $this->calculateTrend($pendingApprovalCount, $previousWeekData->pending_approvals_count);
        $recentReservationsTrend = $this->calculateTrend($recentReservationsCount, $previousWeekData->recent_reservations_count);
        $utilizationRateTrend = $this->calculateTrend($utilizationRate / 100, $previousWeekData->venue_utilization_rate);

        $trendData = [
            'pendingApprovalCount' => $pendingApprovalCount,
            'pendingApprovalTrend' => $pendingApprovalTrend,
            'recentReservationsCount' => $recentReservationsCount,
            'recentReservationsTrend' => $recentReservationsTrend,
            'utilizationRate' => $utilizationRate,
            'utilizationRateTrend' => $utilizationRateTrend,
        ];

        $incomingReservations = Reservation::with(['user', 'noter', 'approver', 'options.venue'])
        ->where('status', "Approved")
        ->orderBy('date', 'asc')
        ->orderBy('start_time', 'asc')
        ->take(5)
        ->get();

        $reservations = Reservation::with(['user', 'noter', 'approver', 'options'])
            ->where('isApproved', true)->get();

        return inertia('Dashboard/Index', [
            'trendData' => $trendData,
            'incomingReservations' => $incomingReservations,
            'reservations' => $reservations
        ]);
    }

    private function calculateTrend($data, $prevData)
    {
        $trend = ($data - $prevData) / ($prevData ?: 1) * 100;

        return [
            'value' => $trend,
            'type' => $this->getTrendType($trend),
        ];
    }

    private function getTrendType($trend)
    {
        if ($trend > 100) {
            return 'alert';
        } elseif ($trend > 0) {
            return 'up';
        } else {
            return 'down';
        }
    }
}
