<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            FormTemplateSeeder::class,
            FormSubmissionSeeder::class,
            FormSubmissionVersionSeeder::class,
        ]);
    }
}
