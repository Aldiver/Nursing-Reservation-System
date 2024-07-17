<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Services\DashboardDataService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateReservationStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reservations:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now()->startOfDay();

        Reservation::where('status', 'Pending')
            ->where('date', '<=', $now)
            ->update(['status' => 'Overdue']);

        (new DashboardDataService())->updateDashboardData();

        $this->info('Reservation statuses updated successfully.');
    }
}
