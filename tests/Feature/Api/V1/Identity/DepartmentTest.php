<?php

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->adminRole = Role::factory()->create(['slug' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($this->adminRole);
    $this->adminToken = Auth::guard('api')->tokenById($this->admin->id);

    $this->user = User::factory()->create();
    $this->userToken = Auth::guard('api')->tokenById($this->user->id);
});

test('admin can list departments', function () {
    Department::factory(3)->create();

    $response = $this->getJson('/api/v1/departments', [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('admin can show department', function () {
    $dept = Department::factory()->create();

    $response = $this->getJson("/api/v1/departments/{$dept->id}", [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $dept->id);
});

test('admin can create department', function () {
    $response = $this->postJson('/api/v1/departments', [
        'name' => 'Engineering',
        'description' => 'The engineering department',
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Engineering');
});

test('non-admin cannot create department', function () {
    $response = $this->postJson('/api/v1/departments', [
        'name' => 'Engineering',
    ], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('non-admin cannot update department', function () {
    $dept = Department::factory()->create();

    $response = $this->putJson("/api/v1/departments/{$dept->id}", [
        'name' => 'New Name',
    ], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('admin can update department', function () {
    $dept = Department::factory()->create();

    $response = $this->putJson("/api/v1/departments/{$dept->id}", [
        'name' => 'New Name',
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'New Name');
});

test('admin can deactivate department', function () {
    $dept = Department::factory()->create(['is_active' => true]);

    $response = $this->putJson("/api/v1/departments/{$dept->id}", [
        'is_active' => false,
    ], [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('departments', ['id' => $dept->id, 'is_active' => false]);
});

test('non-admin cannot deactivate department', function () {
    $dept = Department::factory()->create(['is_active' => true]);

    $response = $this->putJson("/api/v1/departments/{$dept->id}", [
        'is_active' => false,
    ], [
        'Authorization' => "Bearer $this->userToken",
    ]);

    $response->assertForbidden();
});

test('unauthenticated user cannot access department routes', function (string $method, string $uri) {
    /** @var TestCase $this */
    $response = $this->json($method, $uri);

    $response->assertUnauthorized();
})->with([
    'index' => ['GET', '/api/v1/departments'],
    'store' => ['POST', '/api/v1/departments'],
    'show' => ['GET', '/api/v1/departments/1'],
    'update' => ['PUT', '/api/v1/departments/1'],
]);

test('department validation', function (array $data, array $errors) {
    /** @var TestCase $this */
    $response = $this->postJson('/api/v1/departments', $data, [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'missing name' => [[], ['name']],
    'name too long' => [['name' => str_repeat('a', 256)], ['name']],
]);

test('show non-existent department returns 404', function () {
    $response = $this->getJson('/api/v1/departments/999', [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertNotFound();
});

test('soft-deleted department is not in listing', function () {
    $dept = Department::factory()->create();
    $dept->delete();

    $response = $this->getJson('/api/v1/departments', [
        'Authorization' => "Bearer $this->adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonMissing(['id' => $dept->id]);
});
