<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_has_roles_relationship()
    {
        $permission = Permission::factory()->create();
        $role = Role::factory()->create();
        $permission->roles()->attach($role);

        $this->assertCount(1, $permission->fresh()->roles);
        $this->assertInstanceOf(Role::class, $permission->fresh()->roles->first());
    }

    public function test_permission_casts_is_active_to_boolean()
    {
        $permission = Permission::factory()->create(['is_active' => 1]);
        $this->assertIsBool($permission->is_active);
        $this->assertTrue($permission->is_active);
    }
}
