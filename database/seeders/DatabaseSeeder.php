<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Permission;
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
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        // Assign all permissions to admin role
        $adminRole = Role::where('slug', 'admin')->first();
        $adminRole->permissions()->attach(Permission::all());

        // Assign some permissions to user role
        $userRole = Role::where('slug', 'user')->first();
        $userRole->permissions()->attach(
            Permission::whereIn('slug', ['view-users', 'view-departments', 'view-teams', 'view-roles'])->get()
        );

        // Create an admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->roles()->attach($adminRole);

        // Create sample departments, teams, and users
        Department::factory(3)->create()->each(function ($dept) use ($userRole) {
            Team::factory(2)->create(['department_id' => $dept->id])->each(function ($team) use ($dept, $userRole) {
                User::factory(5)->create()->each(function ($user) use ($dept, $team, $userRole) {
                    $user->departments()->attach($dept);
                    $user->teams()->attach($team);
                    $user->roles()->attach($userRole);
                });
            });
        });
    }
}
