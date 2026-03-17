<?php

namespace Database\Factories;

use App\Models\FormTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormTemplate>
 */
class FormTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Form',
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'first_name' => ['type' => 'string'],
                    'last_name' => ['type' => 'string'],
                ],
            ],
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
