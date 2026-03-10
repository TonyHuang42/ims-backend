<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $this->adminRole = Role::factory()->create(['slug' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($this->adminRole);
    
    /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $apiGuard */
    $apiGuard = Auth::guard('api');
    $this->adminToken = $apiGuard->tokenById($this->admin->id);

    $this->user = User::factory()->create();
    $this->userToken = $apiGuard->tokenById($this->user->id);
});

test('admin can list permissions', function () {
    /** @var \Tests\TestCase $this */
    Permission::factory(5)->create();

    $response = $this->getJson('/api/v1/permissions', [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta'])
        ->assertJsonCount(5, 'data');
});

test('admin can show permission', function () {
    /** @var \Tests\TestCase $this */
    $permission = Permission::factory()->create();

    $response = $this->getJson("/api/v1/permissions/{$permission->id}", [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $permission->id);
});

test('admin can create permission', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->postJson('/api/v1/permissions', [
        'name' => 'Test Permission',
        'slug' => 'test-permission',
        'description' => 'Test description',
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.slug', 'test-permission');

    $this->assertDatabaseHas('permissions', ['slug' => 'test-permission']);
});

test('admin can update permission', function () {
    /** @var \Tests\TestCase $this */
    $permission = Permission::factory()->create();

    $response = $this->putJson("/api/v1/permissions/{$permission->id}", [
        'name' => 'Updated Name',
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Name');
});

test('admin can deactivate permission', function () {
    /** @var \Tests\TestCase $this */
    $permission = Permission::factory()->create(['is_active' => true]);

    $response = $this->putJson("/api/v1/permissions/{$permission->id}", [
        'is_active' => false,
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('permissions', ['id' => $permission->id, 'is_active' => false]);
});

test('non-admin cannot create permission', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->postJson('/api/v1/permissions', [
        'name' => 'Test Permission',
        'slug' => 'test-permission',
    ], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('permission validation', function (array $data, array $errors) {
    /** @var TestCase $this */
    $response = $this->postJson('/api/v1/permissions', $data, [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'missing name' => [['slug' => 'test'], ['name']],
    'missing slug' => [['name' => 'Test'], ['slug']],
    'duplicate slug' => [
        fn () => ['name' => 'View Users', 'slug' => Permission::factory()->create(['slug' => 'view-users'])->slug],
        ['slug'],
    ],
]);

test('unauthenticated user cannot access permission routes', function (string $method, string $uri) {
    /** @var TestCase $this */
    $response = $this->json($method, $uri);

    $response->assertUnauthorized();
})->with([
    'index' => ['GET', '/api/v1/permissions'],
    'store' => ['POST', '/api/v1/permissions'],
    'show' => ['GET', '/api/v1/permissions/1'],
    'update' => ['PUT', '/api/v1/permissions/1'],
]);
