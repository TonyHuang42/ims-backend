<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_department_relationship()
    {
        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $user->department);
        $this->assertEquals($department->id, $user->department->id);
    }

    public function test_user_has_team_relationship()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Team::class, $user->team);
        $this->assertEquals($team->id, $user->team->id);
    }

    public function test_user_has_roles_relationship()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->roles()->attach($role);

        $this->assertCount(1, $user->roles);
        $this->assertEquals($role->id, $user->roles->first()->id);
    }

    public function test_user_can_check_if_is_admin()
    {
        $user = User::factory()->create();
        $adminRole = Role::factory()->create(['slug' => 'admin']);

        $this->assertFalse($user->isAdmin());

        $user->roles()->attach($adminRole);

        $this->assertTrue($user->isAdmin());
    }

    public function test_user_uses_soft_deletes()
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_user_factory_with_identity_state()
    {
        $user = User::factory()->withIdentity()->create();

        $this->assertNotNull($user->department_id);
        $this->assertNotNull($user->team_id);
        $this->assertEquals($user->department_id, $user->team->department_id);
    }
}
