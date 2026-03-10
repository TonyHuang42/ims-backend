<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Users', 'slug' => 'view-users'],
            ['name' => 'Create Users', 'slug' => 'create-users'],
            ['name' => 'Update Users', 'slug' => 'update-users'],
            ['name' => 'Delete Users', 'slug' => 'delete-users'],
            ['name' => 'View Departments', 'slug' => 'view-departments'],
            ['name' => 'Create Departments', 'slug' => 'create-departments'],
            ['name' => 'Update Departments', 'slug' => 'update-departments'],
            ['name' => 'Delete Departments', 'slug' => 'delete-departments'],
            ['name' => 'View Teams', 'slug' => 'view-teams'],
            ['name' => 'Create Teams', 'slug' => 'create-teams'],
            ['name' => 'Update Teams', 'slug' => 'update-teams'],
            ['name' => 'Delete Teams', 'slug' => 'delete-teams'],
            ['name' => 'View Roles', 'slug' => 'view-roles'],
            ['name' => 'Create Roles', 'slug' => 'create-roles'],
            ['name' => 'Update Roles', 'slug' => 'update-roles'],
            ['name' => 'Delete Roles', 'slug' => 'delete-roles'],
            ['name' => 'View Permissions', 'slug' => 'view-permissions'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
