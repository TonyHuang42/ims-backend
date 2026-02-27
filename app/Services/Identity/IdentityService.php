<?php

namespace App\Services\Identity;

use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;

class IdentityService implements IdentityServiceInterface
{
    public function findUser(int $id): ?User
    {
        return User::find($id);
    }

    public function getUserRoles(int $userId): Collection
    {
        $user = User::find($userId);

        return $user ? $user->roles : collect();
    }

    public function getUserDepartment(int $userId): ?Department
    {
        $user = User::find($userId);

        return $user ? $user->department : null;
    }

    public function getUserTeam(int $userId): ?Team
    {
        $user = User::find($userId);

        return $user ? $user->team : null;
    }

    public function isUserActive(int $userId): bool
    {
        $user = User::find($userId);

        return $user ? $user->is_active : false;
    }
}
