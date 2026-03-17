<?php

namespace Tests\Unit\Models;

use App\Models\FormSubmission;
use App\Models\FormSubmissionVersion;
use App\Models\FormTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_template_relationship(): void
    {
        $template = FormTemplate::factory()->create();
        $submission = FormSubmission::factory()->create(['form_template_id' => $template->id]);

        $this->assertInstanceOf(FormTemplate::class, $submission->template);
        $this->assertEquals($template->id, $submission->template->id);
    }

    public function test_it_has_versions_relationship(): void
    {
        $submission = FormSubmission::factory()->create();
        FormSubmissionVersion::factory()->create(['submission_id' => $submission->id, 'version_number' => 1]);
        FormSubmissionVersion::factory()->create(['submission_id' => $submission->id, 'version_number' => 2]);

        $this->assertCount(2, $submission->versions);
    }

    public function test_it_has_current_version_relationship(): void
    {
        $submission = FormSubmission::factory()->create();
        $version = FormSubmissionVersion::factory()->create([
            'submission_id' => $submission->id,
            'version_number' => 1,
        ]);
        $submission->update(['current_version_id' => $version->id]);

        $submission->refresh();

        $this->assertInstanceOf(FormSubmissionVersion::class, $submission->currentVersion);
        $this->assertEquals($version->id, $submission->currentVersion->id);
    }
}
