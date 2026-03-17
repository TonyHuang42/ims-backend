<?php

namespace Database\Seeders;

use App\Models\FormTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class FormTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $createdBy = User::query()->first();

        $templates = [
            [
                'name' => 'Contact Request',
                'schema' => [
                    '$schema' => 'https://json-schema.org/draft/2020-12/schema',
                    'type' => 'object',
                    'properties' => [
                        'full_name' => ['type' => 'string', 'title' => 'Full Name'],
                        'email' => ['type' => 'string', 'format' => 'email', 'title' => 'Email'],
                        'subject' => ['type' => 'string', 'title' => 'Subject'],
                        'message' => ['type' => 'string', 'title' => 'Message'],
                    ],
                    'required' => ['full_name', 'email', 'message'],
                ],
            ],
            [
                'name' => 'Incident Report',
                'schema' => [
                    '$schema' => 'https://json-schema.org/draft/2020-12/schema',
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'string', 'title' => 'Incident Title'],
                        'description' => ['type' => 'string', 'title' => 'Description'],
                        'severity' => [
                            'type' => 'string',
                            'enum' => ['low', 'medium', 'high', 'critical'],
                            'title' => 'Severity',
                        ],
                        'reporter_name' => ['type' => 'string', 'title' => 'Reporter Name'],
                        'occurred_at' => ['type' => 'string', 'format' => 'date-time', 'title' => 'Occurred At'],
                    ],
                    'required' => ['title', 'description', 'severity'],
                ],
            ],
            [
                'name' => 'Feedback Survey',
                'schema' => [
                    '$schema' => 'https://json-schema.org/draft/2020-12/schema',
                    'type' => 'object',
                    'properties' => [
                        'rating' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 5, 'title' => 'Rating'],
                        'feedback_text' => ['type' => 'string', 'title' => 'Feedback'],
                        'would_recommend' => ['type' => 'boolean', 'title' => 'Would recommend'],
                    ],
                    'required' => ['rating', 'feedback_text'],
                ],
            ],
        ];

        foreach ($templates as $template) {
            FormTemplate::query()->updateOrCreate(
                ['name' => $template['name']],
                [
                    'schema' => $template['schema'],
                    'created_by' => $createdBy?->id,
                ]
            );
        }
    }
}
