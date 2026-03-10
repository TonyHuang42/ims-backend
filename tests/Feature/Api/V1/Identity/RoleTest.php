<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $this->adminRole = Role::factory()->create(['slug' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($this->adminRole);
    /** @var JWTGuard $apiGuard */
    $apiGuard = Auth::guard('api');
    $this->adminToken = $apiGuard->tokenById($this->admin->id);

    $this->user = User::factory()->create();
    $this->userToken = $apiGuard->tokenById($this->user->id);
});

test('admin can list roles', function () {
    /** @var \Tests\TestCase $this */
    Role::factory(3)->create();

    $response = $this->getJson('/api/v1/roles', [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(4, 'data'); // 3 + 1 from beforeEach
});

test('admin can show role', function () {
    /** @var \Tests\TestCase $this */
    $role = Role::factory()->create();

    $response = $this->getJson("/api/v1/roles/{$role->id}", [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $role->id);
});

test('admin can create role', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->postJson('/api/v1/roles', [
        'name' => 'Manager',
        'slug' => 'manager',
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.slug', 'manager');
});

test('non-admin cannot create role', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->postJson('/api/v1/roles', [
        'name' => 'Manager',
        'slug' => 'manager',
    ], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('admin can update role', function () {
    /** @var \Tests\TestCase $this */
    $role = Role::factory()->create();

    $response = $this->putJson("/api/v1/roles/{$role->id}", [
        'name' => 'New Role Name',
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'New Role Name');
});

test('non-admin cannot update role', function () {
    /** @var \Tests\TestCase $this */
    $role = Role::factory()->create();

    $response = $this->putJson("/api/v1/roles/{$role->id}", [
        'name' => 'New Role Name',
    ], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('admin can deactivate role', function () {
    /** @var \Tests\TestCase $this */
    $role = Role::factory()->create(['is_active' => true]);

    $response = $this->putJson("/api/v1/roles/{$role->id}", [
        'is_active' => false,
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('roles', ['id' => $role->id, 'is_active' => false]);
});

test('non-admin cannot deactivate role', function () {
    /** @var \Tests\TestCase $this */
    $role = Role::factory()->create(['is_active' => true]);

    $response = $this->putJson("/api/v1/roles/{$role->id}", [
        'is_active' => false,
    ], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('role validation', function (array $data, array $errors) {
    /** @var TestCase $this */
    $response = $this->postJson('/api/v1/roles', $data, [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'missing name' => [['slug' => 'test'], ['name']],
    'missing slug' => [['name' => 'Test'], ['slug']],
    'duplicate slug' => [
        fn () => ['name' => 'Admin Duplicate', 'slug' => Role::factory()->create(['slug' => 'admin-duplicate'])->slug],
        ['slug'],
    ],
]);

test('admin can create role with permissions', function () {
    /** @var \Tests\TestCase $this */
    $permission1 = Permission::factory()->create();
    $permission2 = Permission::factory()->create();

    $response = $this->postJson('/api/v1/roles', [
        'name' => 'Manager',
        'slug' => 'manager',
        'permission_ids' => [$permission1->id, $permission2->id],
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertCreated()
        ->assertJsonCount(2, 'data.permissions');
});

test('admin can sync role permissions', function () {
    /** @var \Tests\TestCase $this */
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();

    $response = $this->postJson("/api/v1/roles/{$role->id}/permissions", [
        'permission_ids' => [$permission->id],
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJson(['message' => 'Permissions synced successfully']);

    $this->assertTrue($role->fresh()->permissions->contains($permission->id));
});

test('admin can get role permissions', function () {
    /** @var \Tests\TestCase $this */
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();
    $role->permissions()->attach($permission);

    $response = $this->getJson("/api/v1/roles/{$role->id}/permissions", [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $permission->id);
});

test('non-admin cannot sync role permissions', function () {
    /** @var \Tests\TestCase $this */
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();

    $response = $this->postJson("/api/v1/roles/{$role->id}/permissions", [
        'permission_ids' => [$permission->id],
    ], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('unauthenticated user cannot access role routes', function (string $method, string $uri) {
    /** @var TestCase $this */
    $response = $this->json($method, $uri);

    $response->assertUnauthorized();
})->with([
    'index' => ['GET', '/api/v1/roles'],
    'store' => ['POST', '/api/v1/roles'],
    'show' => ['GET', '/api/v1/roles/1'],
    'update' => ['PUT', '/api/v1/roles/1'],
    'sync-permissions' => ['POST', '/api/v1/roles/1/permissions'],
    'get-permissions' => ['GET', '/api/v1/roles/1/permissions'],
]);

test('show non-existent role returns 404', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->getJson('/api/v1/roles/999', [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertNotFound();
});
