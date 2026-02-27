<?php

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

test('admin can soft-delete role', function () {
    /** @var \Tests\TestCase $this */
    $role = Role::factory()->create();

    $response = $this->deleteJson("/api/v1/roles/{$role->id}", [], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful();
    $this->assertSoftDeleted('roles', ['id' => $role->id]);
});

test('non-admin cannot delete role', function () {
    /** @var \Tests\TestCase $this */
    $role = Role::factory()->create();

    $response = $this->deleteJson("/api/v1/roles/{$role->id}", [], [
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
        fn () => ['name' => 'Admin', 'slug' => 'admin'],
        ['slug'],
    ],
]);

test('unauthenticated user cannot access role routes', function (string $method, string $uri) {
    /** @var TestCase $this */
    $response = $this->json($method, $uri);

    $response->assertUnauthorized();
})->with([
    'index' => ['GET', '/api/v1/roles'],
    'store' => ['POST', '/api/v1/roles'],
    'show' => ['GET', '/api/v1/roles/1'],
    'update' => ['PUT', '/api/v1/roles/1'],
    'destroy' => ['DELETE', '/api/v1/roles/1'],
]);

test('show non-existent role returns 404', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->getJson('/api/v1/roles/999', [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertNotFound();
});
