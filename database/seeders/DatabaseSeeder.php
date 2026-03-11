<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
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

        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
        ]);

        Department::factory(3)->create()->each(function ($dept) use ($userRole) {
            Team::factory(2)->create(['department_id' => $dept->id])->each(function ($team) use ($dept, $userRole) {
                User::factory(5)->create([
                    'role_id' => $userRole->id,
                ])->each(function ($user) use ($dept, $team) {
                    $user->departments()->attach($dept);
                    $user->teams()->attach($team);
                });
            });
        });
    }
}
