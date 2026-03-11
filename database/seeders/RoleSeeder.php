<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(['name' => 'admin'], ['is_active' => true]);
        Role::updateOrCreate(['name' => 'manager'], ['is_active' => true]);
        Role::updateOrCreate(['name' => 'user'], ['is_active' => true]);
    }
}
