<?php

namespace Tests\Feature\Teacher;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_group_and_default_work_processes(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->post(route('teacher.groups.store'), ['name' => 'Groep A'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $group = Group::where('name', 'Groep A')->firstOrFail();
        $this->assertSame($teacher->id, $group->teacher_id);
        $this->assertCount(8, $group->workProcesses);
    }

    public function test_teacher_cannot_add_or_edit_work_processes_in_owned_group(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'Groep A']);
        $process = $group->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);

        $this->actingAs($teacher)->post(route('teacher.groups.work-processes.store', $group), ['name' => 'Nieuw proces'])
            ->assertForbidden();
        $this->patch(route('teacher.groups.work-processes.update', [$group, $process]), ['name' => 'Aangepast'])
            ->assertForbidden();

        $this->assertCount(1, $group->workProcesses()->get());
        $this->assertSame('Plannen', $process->fresh()->name);
    }
    public function test_teacher_cannot_delete_work_process_with_evidence_or_change_other_group(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'Groep A']);
        $process = $group->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);
        $student = User::factory()->create(['role' => 'student']);
        $student->groups()->sync([$group->id]);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        $portfolio->evidence()->create([
            'title' => 'Bewijs', 'status' => 'ingeleverd', 'work_process' => '1',
            'category' => 'Project', 'item_number' => 1,
        ]);

        $this->actingAs($teacher)->delete(route('teacher.groups.work-processes.destroy', [$group, $process]))
            ->assertForbidden();
        $this->actingAs($otherTeacher)->get(route('teacher.groups.show', $group))->assertNotFound();
        $this->patch(route('teacher.groups.update', $group), ['name' => 'Overgenomen'])->assertNotFound();

        $this->assertDatabaseHas('group_work_processes', ['id' => $process->id]);
        $this->assertSame('Groep A', $group->fresh()->name);
    }

    public function test_teacher_can_add_student_but_rejects_non_student_accounts(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'Groep A']);
        $student = User::factory()->create(['role' => 'student']);
        $anotherTeacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->post(route('teacher.groups.students.store', $group), ['student_id' => $student->id])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('teacher.groups.students.store', $group), ['student_id' => $anotherTeacher->id])
            ->assertStatus(422);

        $this->assertTrue($student->fresh()->groups->contains('id', $group->id));
        $this->assertFalse($anotherTeacher->fresh()->groups->contains('id', $group->id));
    }

    public function test_teacher_can_update_student_group_study_and_reviewers(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $secondTeacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $regularGroup = $teacher->teachingGroups()->create(['name' => 'Klas A']);
        $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        $lifecycleGroup = Group::where('lifecycle_status', 'nieuw')->firstOrFail();

        $this->actingAs($teacher)->patch(route('teacher.students.update', $student), [
            'group_ids' => [$regularGroup->id, $lifecycleGroup->id],
            'reviewer_ids' => [$teacher->id, $secondTeacher->id],
            'study_type' => 'BBL',
        ])->assertRedirect(route('teacher.students.show', $student))->assertSessionHasNoErrors();

        $student->refresh();
        $this->assertSame('BBL', $student->study_type);
        $this->assertSame('BBL', $student->leerweg);
        $this->assertSame($regularGroup->id, $student->group_id);
        $this->assertEqualsCanonicalizing([$teacher->id, $secondTeacher->id], $student->reviewers->modelKeys());
        $this->assertSame('BBL', $student->portfolios()->firstOrFail()->study);
    }

    public function test_teacher_student_update_requires_two_teacher_reviewers_and_one_status_group(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $statusGroup = Group::where('lifecycle_status', 'nieuw')->firstOrFail();

        $this->actingAs($teacher)->patch(route('teacher.students.update', $student), [
            'group_ids' => [$statusGroup->id], 'reviewer_ids' => [$teacher->id], 'study_type' => 'BOL',
        ])->assertSessionHasErrors('reviewer_ids');

        $studentAccount = User::factory()->create(['role' => 'student']);
        $this->patch(route('teacher.students.update', $student), [
            'group_ids' => [$statusGroup->id], 'reviewer_ids' => [$teacher->id, $studentAccount->id], 'study_type' => 'BOL',
        ])->assertStatus(422);
    }

    public function test_teacher_cannot_delete_unused_work_process_or_access_fixed_group_settings(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'Klas A']);
        $process = $group->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);
        $fixedGroup = Group::where('lifecycle_status', 'nieuw')->firstOrFail();

        $this->actingAs($teacher)->delete(route('teacher.groups.work-processes.destroy', [$group, $process]))
            ->assertForbidden();
        $this->delete(route('teacher.groups.work-processes.destroy', [$fixedGroup, $process]))->assertForbidden();
        $this->get(route('teacher.groups.settings', $fixedGroup))->assertForbidden();

        $this->assertDatabaseHas('group_work_processes', ['id' => $process->id]);
    }
    public function test_teacher_can_update_group_photo_and_remove_student_only_from_departed_group(): void
    {
        Storage::fake('public');
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'Klas A']);

        $this->actingAs($teacher)->patch(route('teacher.groups.update', $group), [
            'name' => 'Klas B', 'photo' => UploadedFile::fake()->createWithContent(
                'groep.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jBd8AAAAASUVORK5CYII='),
            ),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $photoPath = $group->fresh()->photo_path;
        $this->assertNotNull($photoPath);
        Storage::disk('public')->assertExists($photoPath);
        $this->patch(route('teacher.groups.update', $group), ['name' => 'Klas B', 'remove_photo' => true])
            ->assertRedirect()->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($photoPath);
        $this->assertNull($group->fresh()->photo_path);

        $student = User::factory()->create(['role' => 'student']);
        $departed = Group::where('lifecycle_status', 'weg')->firstOrFail();
        $student->groups()->sync([$departed->id]);
        $this->delete(route('teacher.students.destroy', $student))->assertRedirect(route('teacher.students.index'));
        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }
}
