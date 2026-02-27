<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->adminRole = Role::factory()->create(['slug' => 'admin']);
    $this->userRole = Role::factory()->create(['slug' => 'user']);

    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($this->adminRole);
    $this->adminToken = Auth::guard('api')->tokenById($this->admin->id);

    $this->user = User::factory()->create();
    $this->user->roles()->attach($this->userRole);
    $this->userToken = Auth::guard('api')->tokenById($this->user->id);
});

test('admin can list users', function () {
    $response = $this->getJson('/api/v1/users', [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta']);
});

test('admin can show user', function () {
    $targetUser = User::factory()->create();

    $response = $this->getJson("/api/v1/users/{$targetUser->id}", [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $targetUser->id)
        ->assertJsonStructure(['data' => ['id', 'name', 'email', 'department', 'team', 'roles']]);
});

test('admin can create user', function () {
    $response = $this->postJson('/api/v1/users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'role_ids' => [$this->userRole->id],
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'newuser@example.com');

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

test('admin can create user with department and team', function () {
    $dept = \App\Models\Department::factory()->create();
    $team = \App\Models\Team::factory()->create(['department_id' => $dept->id]);

    $response = $this->postJson('/api/v1/users', [
        'name' => 'Identity User',
        'email' => 'identity@example.com',
        'password' => 'password123',
        'department_id' => $dept->id,
        'team_id' => $team->id,
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.department.id', $dept->id)
        ->assertJsonPath('data.team.id', $team->id);
});

test('non-admin cannot create user', function () {
    $response = $this->postJson('/api/v1/users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
    ], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('admin can update user', function () {
    $targetUser = User::factory()->create();

    $response = $this->putJson("/api/v1/users/{$targetUser->id}", [
        'name' => 'Updated Name',
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Name');
});

test('non-admin cannot update user', function () {
    $targetUser = User::factory()->create();

    $response = $this->putJson("/api/v1/users/{$targetUser->id}", [
        'name' => 'Updated Name',
    ], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('admin can soft-delete user', function () {
    $targetUser = User::factory()->create();

    $response = $this->deleteJson("/api/v1/users/{$targetUser->id}", [], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful();
    $this->assertSoftDeleted('users', ['id' => $targetUser->id]);
});

test('non-admin cannot soft-delete user', function () {
    $targetUser = User::factory()->create();

    $response = $this->deleteJson("/api/v1/users/{$targetUser->id}", [], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('admin can sync user roles', function () {
    $targetUser = User::factory()->create();
    $newRole = Role::factory()->create();

    $response = $this->postJson("/api/v1/users/{$targetUser->id}/roles", [
        'role_ids' => [$newRole->id],
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJson(['message' => 'Roles synced successfully']);

    $this->assertTrue($targetUser->fresh()->roles->contains($newRole->id));
});

test('non-admin cannot sync user roles', function () {
    $targetUser = User::factory()->create();
    $newRole = Role::factory()->create();

    $response = $this->postJson("/api/v1/users/{$targetUser->id}/roles", [
        'role_ids' => [$newRole->id],
    ], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('admin can get user roles', function () {
    $targetUser = User::factory()->create();
    $targetUser->roles()->attach($this->userRole);

    $response = $this->getJson("/api/v1/users/{$targetUser->id}/roles", [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $this->userRole->id);
});

test('user filtering', function (string $filter, $value, int $expectedCount) {
    /** @var TestCase $this */
    $dept = \App\Models\Department::factory()->create();
    $team = \App\Models\Team::factory()->create(['department_id' => $dept->id]);
    $role = Role::factory()->create();

    User::factory()->create([
        'department_id' => $dept->id,
        'team_id' => $team->id,
        'is_active' => true,
    ])->roles()->attach($role);

    // Filter values might need to be resolved if they are closures
    $val = is_callable($value) ? $value($dept, $team, $role) : $value;

    $response = $this->getJson("/api/v1/users?{$filter}={$val}", [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonCount($expectedCount, 'data');
})->with([
    'by department_id' => ['department_id', fn ($d) => $d->id, 1],
    'by team_id' => ['team_id', fn ($d, $t) => $t->id, 1],
    'by is_active' => ['is_active', 1, 3], // admin, user, and the new one
    'by role_id' => ['role_id', fn ($d, $t, $r) => $r->id, 1],
]);

test('user validation', function (array $data, array $errors) {
    /** @var TestCase $this */
    $response = $this->postJson('/api/v1/users', $data, [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'missing name' => [['email' => 'test@example.com', 'password' => 'password123'], ['name']],
    'missing email' => [['name' => 'Test', 'password' => 'password123'], ['email']],
    'invalid email' => [['name' => 'Test', 'email' => 'not-an-email', 'password' => 'password123'], ['email']],
    'duplicate email' => [
        fn () => ['name' => 'Test', 'email' => User::factory()->create()->email, 'password' => 'password123'],
        ['email'],
    ],
    'invalid role_ids' => [['name' => 'Test', 'email' => 't@e.com', 'password' => 'p123', 'role_ids' => [999]], ['role_ids.0']],
]);

test('unauthenticated user cannot access user routes', function (string $method, string $uri) {
    /** @var TestCase $this */
    $response = $this->json($method, $uri);

    $response->assertUnauthorized();
})->with([
    'index' => ['GET', '/api/v1/users'],
    'store' => ['POST', '/api/v1/users'],
    'show' => ['GET', '/api/v1/users/1'],
    'update' => ['PUT', '/api/v1/users/1'],
    'destroy' => ['DELETE', '/api/v1/users/1'],
    'sync-roles' => ['POST', '/api/v1/users/1/roles'],
    'get-roles' => ['GET', '/api/v1/users/1/roles'],
]);

test('show non-existent user returns 404', function () {
    $response = $this->getJson('/api/v1/users/999', [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertNotFound();
});
