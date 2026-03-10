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

    public function test_user_has_departments_relationship()
    {
        $department = Department::factory()->create();
        $user = User::factory()->create();
        $user->departments()->attach($department);

        $this->assertCount(1, $user->fresh()->departments);
        $this->assertEquals($department->id, $user->fresh()->departments->first()->id);
    }

    public function test_user_has_teams_relationship()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $user->teams()->attach($team);

        $this->assertCount(1, $user->fresh()->teams);
        $this->assertEquals($team->id, $user->fresh()->teams->first()->id);
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

    public function test_user_factory_with_identity_state()
    {
        $user = User::factory()->withIdentity()->create();

        $this->assertCount(1, $user->departments);
        $this->assertCount(1, $user->teams);
        $this->assertEquals($user->departments->first()->id, $user->teams->first()->department_id);
    }
}
