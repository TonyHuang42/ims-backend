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

test('getUserRole returns role or null', function () {
    $role = Role::factory()->create();
    $user = User::factory()->create(['role_id' => $role->id]);

    $fetched = $this->service->getUserRole($user->id);

    expect($fetched)->not->toBeNull()
        ->id->toBe($role->id);

    expect($this->service->getUserRole(999))->toBeNull();

    $userNoRole = User::factory()->create(['role_id' => null]);
    expect($this->service->getUserRole($userNoRole->id))->toBeNull();
});

test('getUserDepartments returns departments collection', function () {
    $dept = Department::factory()->create();
    $user = User::factory()->create();
    $user->departments()->attach($dept);

    $departments = $this->service->getUserDepartments($user->id);

    expect($departments)->toHaveCount(1)
        ->first()->id->toBe($dept->id);

    $userNoDept = User::factory()->create();
    expect($this->service->getUserDepartments($userNoDept->id))->toBeEmpty();
    expect($this->service->getUserDepartments(999))->toBeEmpty();
});

test('getUserTeams returns teams collection', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $user->teams()->attach($team);

    $teams = $this->service->getUserTeams($user->id);

    expect($teams)->toHaveCount(1)
        ->first()->id->toBe($team->id);

    $userNoTeam = User::factory()->create();
    expect($this->service->getUserTeams($userNoTeam->id))->toBeEmpty();
    expect($this->service->getUserTeams(999))->toBeEmpty();
});

test('isUserActive returns correct boolean', function () {
    $activeUser = User::factory()->create(['is_active' => true]);
    $inactiveUser = User::factory()->create(['is_active' => false]);

    expect($this->service->isUserActive($activeUser->id))->toBeTrue();
    expect($this->service->isUserActive($inactiveUser->id))->toBeFalse();
    expect($this->service->isUserActive(999))->toBeFalse();
});
