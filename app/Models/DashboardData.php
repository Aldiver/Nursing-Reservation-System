<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardData extends Model
{
    use HasFactory;

    protected $fillable = [
        'week_start_date',
        'pending_approvals_count',
        'recent_reservations_count',
        'venue_utilization_rate'];
}
