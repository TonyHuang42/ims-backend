<?php

use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = Auth::guard('api')->tokenById($this->user->id);
});

test('user can list form templates', function () {
    FormTemplate::factory(3)->create(['is_active' => true]);
    FormTemplate::factory(2)->create(['is_active' => false]);

    $response = $this->getJson('/api/v1/form-templates', [
        'Authorization' => "Bearer $this->token",
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('admin user can see inactive form templates in list', function () {
    FormTemplate::factory(3)->create(['is_active' => true]);
    FormTemplate::factory(2)->create(['is_active' => false]);

    $admin = User::factory()->create();
    $role = Role::factory()->create(['name' => 'admin', 'is_active' => true]);
    $admin->update(['role_id' => $role->id]);

    $adminToken = Auth::guard('api')->tokenById($admin->id);

    $response = $this->getJson('/api/v1/form-templates', [
        'Authorization' => "Bearer $adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data');
});

test('form template search filter returns matching templates only', function () {
    FormTemplate::factory()->create(['name' => 'Registration Form']);
    FormTemplate::factory()->create(['name' => 'Survey Form']);

    $response = $this->getJson('/api/v1/form-templates?search=Registration', [
        'Authorization' => "Bearer $this->token",
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Registration Form');
});

test('user can show form template', function () {
    $template = FormTemplate::factory()->create();

    $response = $this->getJson("/api/v1/form-templates/{$template->id}", [
        'Authorization' => "Bearer $this->token",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $template->id)
        ->assertJsonPath('data.name', $template->name);
});

test('user can create form template', function () {
    $data = [
        'name' => 'New Form',
        'schema' => ['type' => 'object'],
    ];

    $response = $this->postJson('/api/v1/form-templates', $data, [
        'Authorization' => "Bearer $this->token",
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New Form')
        ->assertJsonPath('data.created_by', $this->user->id);
});

test('user can update form template', function () {
    $template = FormTemplate::factory()->create();

    $response = $this->putJson("/api/v1/form-templates/{$template->id}", [
        'name' => 'Updated Name',
        'is_active' => false,
    ], [
        'Authorization' => "Bearer $this->token",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.is_active', false);
});

test('form template validation', function (array $data, array $errors) {
    $response = $this->postJson('/api/v1/form-templates', $data, [
        'Authorization' => "Bearer $this->token",
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'missing name' => [['schema' => []], ['name']],
    'missing schema' => [['name' => 'Test'], ['schema']],
    'invalid schema type' => [['name' => 'Test', 'schema' => 'not-an-array'], ['schema']],
]);

test('unauthenticated user cannot access form template routes', function (string $method, string $uri) {
    $response = $this->json($method, $uri);

    $response->assertUnauthorized();
})->with([
    'index' => ['GET', '/api/v1/form-templates'],
    'store' => ['POST', '/api/v1/form-templates'],
    'show' => ['GET', '/api/v1/form-templates/1'],
    'update' => ['PUT', '/api/v1/form-templates/1'],
]);

test('show non-existent form template returns 404', function () {
    $response = $this->getJson('/api/v1/form-templates/999', [
        'Authorization' => "Bearer $this->token",
    ]);

    $response->assertNotFound();
});

test('update non-existent form template returns 404', function () {
    $response = $this->putJson('/api/v1/form-templates/999', [
        'name' => 'Updated Name',
    ], [
        'Authorization' => "Bearer $this->token",
    ]);

    $response->assertNotFound();
});

test('form templates list is paginated correctly', function () {
    FormTemplate::factory(20)->create();

    $response = $this->getJson('/api/v1/form-templates?per_page=5', [
        'Authorization' => "Bearer $this->token",
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.total', 20)
        ->assertJsonPath('meta.per_page', 5);
});

test('non-admin cannot show inactive form template', function () {
    $template = FormTemplate::factory()->create(['is_active' => false]);

    $response = $this->getJson("/api/v1/form-templates/{$template->id}", [
        'Authorization' => "Bearer $this->token",
    ]);

    $response->assertForbidden();
});

test('admin can show inactive form template', function () {
    $admin = User::factory()->create();
    $role = Role::factory()->create(['name' => 'admin', 'is_active' => true]);
    $admin->update(['role_id' => $role->id]);
    $adminToken = Auth::guard('api')->tokenById($admin->id);

    $template = FormTemplate::factory()->create(['is_active' => false]);

    $response = $this->getJson("/api/v1/form-templates/{$template->id}", [
        'Authorization' => "Bearer $adminToken",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $template->id)
        ->assertJsonPath('data.is_active', false);
});

test('user can update form template with partial data', function () {
    $template = FormTemplate::factory()->create(['name' => 'Original', 'is_active' => true]);

    $response = $this->putJson("/api/v1/form-templates/{$template->id}", [
        'is_active' => false,
    ], [
        'Authorization' => "Bearer $this->token",
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Original')
        ->assertJsonPath('data.is_active', false);
});
