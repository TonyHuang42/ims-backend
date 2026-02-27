<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(['slug' => 'admin'], [
            'name' => 'Administrator',
            'description' => 'System administrator with full access.',
        ]);

        Role::updateOrCreate(['slug' => 'user'], [
            'name' => 'Standard User',
            'description' => 'Standard user with limited access.',
        ]);
    }
}
