<?php

namespace App\Models;

use App\Services\DashboardDataService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'start_time', 'end_time', 'purpose', 'remarks',
        'isNoted', 'isApproved', 'user_id',
        'department_id', 'noted_by', 'approved_by', 'status'
    ];

    protected $casts = [
        'purpose' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($reservation) {
            (new DashboardDataService())->updateDashboardData();
        });

        static::updated(function ($reservation) {
            (new DashboardDataService())->updateDashboardData();
        });

        static::deleted(function ($reservation) {
            (new DashboardDataService())->updateDashboardData();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function options()
    {
        return $this->belongsToMany(Option::class)->withPivot('pax')->withTimestamps();
    }

    public function noter()
    {
        return $this->belongsTo(User::class, 'noted_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }


}
