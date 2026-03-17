<?php

namespace Database\Seeders;

use App\Models\FormSubmission;
use App\Models\FormTemplate;
use Illuminate\Database\Seeder;

class FormSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = FormTemplate::query()->get();

        if ($templates->isEmpty()) {
            return;
        }

        foreach ($templates as $template) {
            FormSubmission::factory()->count(3)->create([
                'form_template_id' => $template->id,
                'current_version_id' => null,
            ]);
        }
    }
}
