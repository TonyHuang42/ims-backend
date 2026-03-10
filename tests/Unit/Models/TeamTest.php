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
        $user = User::factory()->create();
        $team->users()->attach($user);

        $this->assertCount(1, $team->fresh()->users);
        $this->assertInstanceOf(User::class, $team->fresh()->users->first());
    }

    public function test_team_casts_is_active_to_boolean()
    {
        $team = Team::factory()->create(['is_active' => 1]);
        $this->assertIsBool($team->is_active);
        $this->assertTrue($team->is_active);
    }
}
