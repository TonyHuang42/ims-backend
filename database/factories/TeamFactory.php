<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word().' Team',
            'department_id' => Department::factory(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
