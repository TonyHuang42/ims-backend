<?php

namespace Tests\Unit\Models;

use App\Models\FormSubmission;
use App\Models\FormSubmissionVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_submission_relationship(): void
    {
        $submission = FormSubmission::factory()->create();
        $version = FormSubmissionVersion::factory()->create(['submission_id' => $submission->id]);

        $this->assertInstanceOf(FormSubmission::class, $version->submission);
        $this->assertEquals($submission->id, $version->submission->id);
    }

    public function test_it_uses_auto_incrementing_integer_id(): void
    {
        $version = FormSubmissionVersion::factory()->create();
        $this->assertIsInt($version->id);
        $this->assertGreaterThan(0, $version->id);
    }

    public function test_it_has_user_relationship(): void
    {
        $user = User::factory()->create();
        $version = FormSubmissionVersion::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $version->user);
        $this->assertEquals($user->id, $version->user->id);
    }

    public function test_it_casts_content_to_array(): void
    {
        $content = ['data' => 'test'];
        $version = FormSubmissionVersion::factory()->create(['content' => $content]);

        $this->assertIsArray($version->content);
        $this->assertEquals($content, $version->content);
    }
}
