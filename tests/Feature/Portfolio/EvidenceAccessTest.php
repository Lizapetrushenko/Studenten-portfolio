<?php

namespace Tests\Feature\Portfolio;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_owner_and_linked_teacher_can_open_submitted_evidence_files(): void
    {
        Storage::fake('local');
        $teacher = User::factory()->create(['role' => 'teacher']);
        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'Klas A']);
        $student = User::factory()->create(['role' => 'student']);
        $student->groups()->sync([$group->id]);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        Storage::disk('local')->put('evidence/report.pdf', 'PDF content');
        $item = $portfolio->evidence()->create([
            'title' => 'Ingeleverd bewijs', 'status' => 'ingeleverd', 'work_process' => '1',
            'category' => 'Project', 'item_number' => 1, 'file_path' => 'evidence/report.pdf',
            'original_file_name' => 'report.pdf',
        ]);

        $this->actingAs($student)->get(route('evidence.open', $item))->assertOk();
        $this->actingAs($teacher)->get(route('evidence.download', $item))->assertOk();
        $this->actingAs($otherTeacher)->get(route('evidence.open', $item))->assertNotFound();

        $item->update(['status' => 'idee']);
        $this->actingAs($teacher)->get(route('evidence.open', $item))->assertNotFound();
        $this->actingAs($student)->get(route('evidence.download', $item))->assertOk();
    }

    public function test_file_route_returns_not_found_when_evidence_has_no_file(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        $item = $portfolio->evidence()->create([
            'title' => 'Zonder bestand', 'status' => 'ingeleverd', 'work_process' => '1',
            'category' => 'Project', 'item_number' => 1,
        ]);

        $this->actingAs($student)->get(route('evidence.open', $item))->assertNotFound();
    }
}
