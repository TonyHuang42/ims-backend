<?php

namespace App\Services\Identity;

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

    public function getUserDepartments(int $userId): Collection
    {
        $user = User::find($userId);

        return $user ? $user->departments : collect();
    }

    public function getUserTeams(int $userId): Collection
    {
        $user = User::find($userId);

        return $user ? $user->teams : collect();
    }

    public function isUserActive(int $userId): bool
    {
        $user = User::find($userId);

        return $user ? $user->is_active : false;
    }
}
