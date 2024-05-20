<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
{
    use HandlesAuthorization;

    public function noter(User $user)
    {
        return $user->hasPermissionTo('noter');
    }

    public function approver(User $user)
    {
        return $user->hasPermissionTo('approver');
    }
}
