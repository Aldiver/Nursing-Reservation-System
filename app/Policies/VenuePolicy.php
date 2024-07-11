<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Auth\Access\HandlesAuthorization;

class VenuePolicy
{
    use HandlesAuthorization;
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        return $user->can('show');
    }

    public function view(User $user, Venue $venue)
    {
        return $user->can('show');
    }

    public function create(User $user)
    {
        return $user->can('create');
    }

    public function update(User $user, Venue $venue)
    {
        return $user->can('edit');
    }

    public function delete(User $user, Venue $venue)
    {
        return $user->can('delete');
    }
}
