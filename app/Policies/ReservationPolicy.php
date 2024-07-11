<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reservation;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('show');
    }

    public function view(User $user, Reservation $reservation)
    {
        return $user->can('show');
    }

    public function create(User $user)
    {
        return $user->can('create');
    }

    public function update(User $user, Reservation $reservation)
    {
        return $user->can('edit');
    }

    public function delete(User $user, Reservation $reservation)
    {
        return $user->can('delete');
    }

    public function note(User $user, Reservation $reservation)
    {
        return $user->can('note');
    }

    public function approve(User $user, Reservation $reservation)
    {
        return $user->can('approve');
    }
}
