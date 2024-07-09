<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $fillable = ['venue_id', 'name', 'with_pax', 'pax', 'max_pax'];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class)->withPivot('pax')->withTimestamps();
    }
}
