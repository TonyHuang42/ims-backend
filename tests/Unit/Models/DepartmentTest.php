<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_has_teams_relationship()
    {
        $department = Department::factory()->create();
        Team::factory(3)->create(['department_id' => $department->id]);

        $this->assertCount(3, $department->teams);
        $this->assertInstanceOf(Team::class, $department->teams->first());
    }

    public function test_department_has_users_relationship()
    {
        $department = Department::factory()->create();
        User::factory(2)->create(['department_id' => $department->id]);

        $this->assertCount(2, $department->users);
        $this->assertInstanceOf(User::class, $department->users->first());
    }

    public function test_department_uses_soft_deletes()
    {
        $department = Department::factory()->create();
        $department->delete();

        $this->assertSoftDeleted('departments', ['id' => $department->id]);
    }

    public function test_department_casts_is_active_to_boolean()
    {
        $department = Department::factory()->create(['is_active' => 1]);
        $this->assertIsBool($department->is_active);
        $this->assertTrue($department->is_active);
    }
}
