<?php

namespace App\Http\Controllers;

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

        // Calculate current and previous week dates
        $currentWeek = Carbon::now()->startOfWeek();
        $previousWeek = Carbon::now()->startOfWeek()->subWeek();
        $endOfWeek = $currentWeek->copy()->endOfWeek();

        // Calculate the required metrics
        $pendingApprovalCount = Reservation::where('isNoted', true)
            ->where('isApproved', false)
            ->whereBetween('date', [$currentWeek, $currentWeek->copy()->endOfWeek()])
            ->count();

        $recentReservationsCount = Reservation::where('isApproved', true)
            ->whereBetween('date', [$currentWeek, $currentWeek->copy()->endOfWeek()])
            ->count();

        $approvedReservations = Reservation::where('isApproved', true)
        ->whereBetween('date', [$currentWeek, $currentWeek->copy()->endOfWeek()])->get();

        // dd($approvedReservations);

        $utilizationRate = $this->calculateUtilizationRate($approvedReservations);

        // Calculate trends
        $pendingApprovalTrend = $this->calculateTrend('pending_approval', $pendingApprovalCount, $previousWeek, $currentWeek);
        $recentReservationsTrend = $this->calculateTrend('recent_reservations', $recentReservationsCount, $previousWeek, $currentWeek);
        $utilizationRateTrend = $this->calculateTrend('utilization_rate', ($utilizationRate / 100), $previousWeek, $currentWeek);

        // Save trends in the database (optional)
        Trend::updateOrCreate(['metric' => 'pending_approval'], ['value' => $pendingApprovalCount, 'trend' => $pendingApprovalTrend]);
        Trend::updateOrCreate(['metric' => 'recent_reservations'], ['value' => $recentReservationsCount, 'trend' => $recentReservationsTrend]);
        Trend::updateOrCreate(['metric' => 'utilization_rate'], ['value' => $utilizationRate, 'trend' => $utilizationRateTrend]);

        $trendData = [
            'pendingApprovalCount' => $pendingApprovalCount,
            'pendingApprovalTrend' => $pendingApprovalTrend,
            'recentReservationsCount' => $recentReservationsCount,
            'recentReservationsTrend' => $recentReservationsTrend,
            'utilizationRate' => $utilizationRate,
            'utilizationRateTrend' => $utilizationRateTrend,
        ];

        $incomingReservations = Reservation::with(['user', 'noter', 'approver', 'options.venue'])
        ->where('isApproved', true)
        ->whereBetween('date', [$currentWeek, $endOfWeek])
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

    //Add to service later
    private function calculateUtilizationRate($reservations)
    {
        // Calculate the utilization rate based on reservations and total available slots
        // For simplicity, assume total slots = 100
        $totalSlots = Option::All()->count();
        $usedSlots = 0;

        foreach ($reservations as $reservation) {
            $startTime = Carbon::parse($reservation->start_time);
            $endTime = Carbon::parse($reservation->end_time);
            $usedSlots += $endTime->diffInHours($startTime);
        }

        return round((($usedSlots / $totalSlots) * 100), 2);
    }

    private function calculateTrend($metric, $currentValue, $previousWeek, $currentWeek)
    {
        $previousValue = Trend::where('metric', $metric)->whereBetween('created_at', [$previousWeek, $previousWeek->copy()->endOfWeek()])->value('value') ?? 0;
        $trend = ($currentValue - $previousValue) / ($previousValue ?: 1) * 100;

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

    private function getWeeklyTrends($metric)
    {
        // Initialize an array to store trends
        $weeklyTrends = [];

        // Fetch trends for each of the last 8 weeks
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $weekTrend = Trend::where('metric', $metric)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->pluck('value')
                ->toArray();

            // Push trend data for the current week to the array
            $weeklyTrends[] = [
                'week' => $weekStart->format('Y-m-d'),
                'values' => $weekTrend,
            ];
        }

        return $weeklyTrends;
    }

}
