<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\DashboardData;

class DashboardDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = Carbon::now()->startOfWeek()->subWeek();
        $dashboardData = DashboardData::where('week_start_date', $startDate)->first();
        echo $startDate;
        
        if (!$dashboardData) {
            DashboardData::create([
                'week_start_date' => $startDate,
                'pending_approvals_count' => 0,
                'recent_reservations_count' => 0,
                'venue_utilization_rate' => 0.00,
            ]);
        }
    }
}
