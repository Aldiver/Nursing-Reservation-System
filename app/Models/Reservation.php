<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'start_time', 'end_time', 'purpose',
        'isNoted', 'isApproved', 'user_id',
        'department_id', 'noted_by', 'approved_by'
    ];

    protected $casts = [
        'purpose' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function options()
    {
        return $this->belongsToMany(Option::class);
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
