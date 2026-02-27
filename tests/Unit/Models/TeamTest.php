<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_has_department_relationship()
    {
        $department = Department::factory()->create();
        $team = Team::factory()->create(['department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $team->department);
        $this->assertEquals($department->id, $team->department->id);
    }

    public function test_team_has_users_relationship()
    {
        $team = Team::factory()->create();
        User::factory(2)->create(['team_id' => $team->id]);

        $this->assertCount(2, $team->users);
        $this->assertInstanceOf(User::class, $team->users->first());
    }

    public function test_team_uses_soft_deletes()
    {
        $team = Team::factory()->create();
        $team->delete();

        $this->assertSoftDeleted('teams', ['id' => $team->id]);
    }

    public function test_team_casts_is_active_to_boolean()
    {
        $team = Team::factory()->create(['is_active' => 1]);
        $this->assertIsBool($team->is_active);
        $this->assertTrue($team->is_active);
    }
}
