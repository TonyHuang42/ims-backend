<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Role $role): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $role->is_active;
    }

    public function viewInactive(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Role $role): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Role $role): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return $user->isAdmin();
    }
}
