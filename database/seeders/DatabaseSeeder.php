<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        // Create an admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->roles()->attach(\App\Models\Role::where('slug', 'admin')->first());

        // Create sample departments, teams, and users
        Department::factory(3)->create()->each(function ($dept) {
            Team::factory(2)->create(['department_id' => $dept->id])->each(function ($team) use ($dept) {
                User::factory(5)->create([
                    'department_id' => $dept->id,
                    'team_id' => $team->id,
                ])->each(function ($user) {
                    $user->roles()->attach(\App\Models\Role::where('slug', 'user')->first());
                });
            });
        });
    }
}
