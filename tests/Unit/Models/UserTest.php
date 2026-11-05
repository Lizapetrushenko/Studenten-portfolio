<?php

namespace Tests\Unit\Models;

use App\Models\Evidence;
use App\Models\Group;
use App\Models\GroupWorkProcess;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_name_uses_preferred_name_for_students_and_full_name_for_staff(): void
    {
        $student = User::factory()->create([
            'role' => 'student', 'first_name' => 'Anna', 'last_name' => 'Jansen', 'preferred_name' => 'Annie',
        ]);
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => '  Docent Voorbeeld  ']);

        $this->assertSame('Annie Jansen', $student->displayName());
        $this->assertSame('Docent Voorbeeld', $teacher->displayName());
    }

    public function test_student_lifecycle_group_helpers_report_portfolio_permissions(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $newGroup = Group::where('lifecycle_status', 'nieuw')->firstOrFail();
        $student->groups()->sync([$newGroup->id]);
        $this->assertFalse($student->portfolioIsLocked());
        $this->assertFalse($student->canOnlySubmitEvidence());

        $retake = Group::where('lifecycle_status', 'herkansen')->firstOrFail();
        $student->groups()->sync([$retake->id]);
        $this->assertTrue($student->canOnlySubmitEvidence());
        $this->assertFalse($student->portfolioIsLocked());

        $graduated = Group::where('lifecycle_status', 'geslaagd')->firstOrFail();
        $student->groups()->sync([$graduated->id]);
        $this->assertTrue($student->portfolioIsLocked());
    }

    public function test_user_group_portfolio_and_evidence_relationships_are_bidirectional(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $group = $teacher->teachingGroups()->create(['name' => 'Klas A']);
        $process = $group->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);
        $student->groups()->sync([$group->id]);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        $evidence = $portfolio->evidence()->create([
            'title' => 'Bewijs', 'work_process' => '1', 'item_number' => 1, 'category' => 'Project', 'status' => 'idee',
        ]);

        $this->assertTrue($teacher->teachingGroups->contains('id', $group->id));
        $this->assertTrue($group->students->contains('id', $student->id));
        $this->assertInstanceOf(Group::class, $process->group);
        $this->assertInstanceOf(User::class, $portfolio->user);
        $this->assertInstanceOf(Portfolio::class, $evidence->portfolio);
        $this->assertInstanceOf(Evidence::class, $portfolio->evidence->first());
        $this->assertInstanceOf(GroupWorkProcess::class, $group->workProcesses->first());
    }
}
