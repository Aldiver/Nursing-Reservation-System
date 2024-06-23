<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trend extends Model
{
    use HasFactory;

    // The attributes that are mass assignable
    protected $fillable = [
        'metric', // The name of the metric (e.g., 'pending_approval', 'recent_reservations', 'utilization_rate')
        'value',  // The value of the metric
        'trend',  // The trend data, typically a JSON object with trend information
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'trend' => 'array', // Cast 'trend' to an array
    ];
}
