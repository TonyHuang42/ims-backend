<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Department',
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
