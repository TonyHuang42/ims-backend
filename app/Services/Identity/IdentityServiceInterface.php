<?php

namespace App\Services\Identity;

use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;

interface IdentityServiceInterface
{
    public function findUser(int $id): ?User;

    public function getUserRoles(int $userId): Collection;

    public function getUserDepartment(int $userId): ?Department;

    public function getUserTeam(int $userId): ?Team;

    public function isUserActive(int $userId): bool;
}
