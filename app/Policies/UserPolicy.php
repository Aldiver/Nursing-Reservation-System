<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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

    public function view(User $user)
    {
        return $user->can('show');
    }

    public function create(User $user)
    {
        return $user->can('create');
    }

    public function update(User $user)
    {
        return $user->can('edit');
    }

    public function delete(User $user)
    {
        return $user->can('delete');
    }
}
