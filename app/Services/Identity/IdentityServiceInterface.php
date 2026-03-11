<?php

namespace App\Services\Identity;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

interface IdentityServiceInterface
{
    public function findUser(int $id): ?User;

    public function getUserRole(int $userId): ?Role;

    public function getUserDepartments(int $userId): Collection;

    public function getUserTeams(int $userId): Collection;

    public function isUserActive(int $userId): bool;
}
