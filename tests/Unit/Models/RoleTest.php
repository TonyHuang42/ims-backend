<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_has_users_relationship()
    {
        $role = Role::factory()->create();
        $user = User::factory()->create();
        $role->users()->attach($user);

        $this->assertCount(1, $role->fresh()->users);
        $this->assertInstanceOf(User::class, $role->fresh()->users->first());
    }

    public function test_role_has_permissions_relationship()
    {
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();
        $role->permissions()->attach($permission);

        $this->assertCount(1, $role->fresh()->permissions);
        $this->assertInstanceOf(Permission::class, $role->fresh()->permissions->first());
    }

    public function test_role_casts_is_active_to_boolean()
    {
        $role = Role::factory()->create(['is_active' => 1]);
        $this->assertIsBool($role->is_active);
        $this->assertTrue($role->is_active);
    }
}
