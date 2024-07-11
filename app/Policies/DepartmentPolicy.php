<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Department;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
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

    public function view(User $user, Department $department)
    {
        return $user->can('show');
    }

    public function create(User $user)
    {
        return $user->can('create');
    }

    public function update(User $user, Department $department)
    {
        return $user->can('edit');
    }

    public function delete(User $user, Department $department)
    {
        return $user->can('delete');
    }
}
