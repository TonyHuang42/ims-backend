<?php

use App\Models\Department;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Services\Identity\IdentityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = new IdentityService;
});

test('findUser returns user or null', function () {
    $user = User::factory()->create();

    expect($this->service->findUser($user->id))->toBeInstanceOf(User::class)
        ->id->toBe($user->id);

    expect($this->service->findUser(999))->toBeNull();
});

test('getUserRoles returns roles collection', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $user->roles()->attach($role);

    $roles = $this->service->getUserRoles($user->id);

    expect($roles)->toHaveCount(1)
        ->first()->id->toBe($role->id);

    expect($this->service->getUserRoles(999))->toBeEmpty();
});

test('getUserDepartment returns department or null', function () {
    $dept = Department::factory()->create();
    $user = User::factory()->create(['department_id' => $dept->id]);

    expect($this->service->getUserDepartment($user->id))->toBeInstanceOf(Department::class)
        ->id->toBe($dept->id);

    $userNoDept = User::factory()->create();
    expect($this->service->getUserDepartment($userNoDept->id))->toBeNull();
    expect($this->service->getUserDepartment(999))->toBeNull();
});

test('getUserTeam returns team or null', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);

    expect($this->service->getUserTeam($user->id))->toBeInstanceOf(Team::class)
        ->id->toBe($team->id);

    $userNoTeam = User::factory()->create();
    expect($this->service->getUserTeam($userNoTeam->id))->toBeNull();
    expect($this->service->getUserTeam(999))->toBeNull();
});

test('isUserActive returns correct boolean', function () {
    $activeUser = User::factory()->create(['is_active' => true]);
    $inactiveUser = User::factory()->create(['is_active' => false]);

    expect($this->service->isUserActive($activeUser->id))->toBeTrue();
    expect($this->service->isUserActive($inactiveUser->id))->toBeFalse();
    expect($this->service->isUserActive(999))->toBeFalse();
});
