<?php
namespace Tests\Feature;
use App\Models\EducationProgram;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_program_and_admin_settings_are_available(): void
    {
        $program = EducationProgram::where('name', 'Software Developer')->firstOrFail();
        $this->assertCount(8, $program->workProcesses);
        $this->actingAs(User::factory()->create(['role' => 'admin', 'mfa_enabled' => true]))
            ->get(route('admin.settings'))->assertOk()->assertSee('KD en opleiding instellen')->assertSee('Software Developer')->assertSee('K1W1')->assertSee('K2W3');
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Opleidingen en werkprocessen')->assertDontSee('Admin dashboard');
    }

    public function test_teacher_cannot_access_or_change_work_process_settings(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'mfa_enabled' => true]);
        $group = $teacher->teachingGroups()->create(['name' => 'SD3A']);
        $process = $group->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);
        $program = EducationProgram::firstOrFail();
        $this->actingAs($teacher)->get(route('teacher.groups.show', $group))->assertOk()->assertDontSee('Instellingen werkprocessen');
        $this->get(route('teacher.dashboard'))->assertOk()->assertDontSee('Werkprocessen beheren');
        $this->get(route('teacher.groups.settings', $group))->assertForbidden();
        $this->get(route('teacher.work-processes.index'))->assertForbidden();
        $this->patch(route('teacher.groups.work-processes.update', [$group, $process]), ['name' => 'Verboden'])->assertForbidden();
        $this->post(route('teacher.groups.work-processes.store', $group), ['name' => 'Verboden'])->assertForbidden();
        $this->delete(route('teacher.groups.work-processes.destroy', [$group, $process]))->assertForbidden();
        $this->get(route('admin.settings'))->assertForbidden();
        $this->post(route('admin.processes.store', $program), ['name' => 'Verboden'])->assertForbidden();
        $this->assertDatabaseHas('group_work_processes', ['id' => $process->id, 'name' => 'Plannen']);
    }

    public function test_admin_changes_reach_groups_and_evidence_prevents_deletion(): void
    {
        $program = EducationProgram::firstOrFail();
        $process = $program->workProcesses()->firstOrFail();
        $group = Group::create(['name' => 'SD3A', 'education_program_id' => $program->id]);
        $group->workProcesses()->create(['number' => $process->number, 'name' => $process->name]);
        $student = User::factory()->create(['role' => 'student']);
        $group->students()->attach($student);
        $portfolio = $student->portfolios()->create(['title' => 'Examen']);
        $evidence = $portfolio->evidence()->create(['work_process' => (string) $process->number, 'title' => 'Plan', 'category' => 'Project', 'item_number' => 1]);
        $admin = User::factory()->create(['role' => 'admin', 'mfa_enabled' => true]);
        $this->actingAs($admin)->patch(route('admin.processes.update', [$program, $process]), ['name' => 'Nieuw plannen'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('group_work_processes', ['group_id' => $group->id, 'number' => 1, 'name' => 'Nieuw plannen']);
        $this->delete(route('admin.processes.destroy', [$program, $process]))->assertRedirect()->assertSessionHasErrors('work_process');
        $this->assertDatabaseHas('evidence', ['id' => $evidence->id]);
        $this->assertDatabaseHas('education_work_processes', ['id' => $process->id]);
        $this->post(route('admin.processes.store', $program), ['name' => 'Reflecteren'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('group_work_processes', ['group_id' => $group->id, 'number' => 9, 'name' => 'Reflecteren']);
        $emptyProcess = $program->workProcesses()->where('number', 9)->firstOrFail();
        $this->delete(route('admin.processes.destroy', [$program, $emptyProcess]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('group_work_processes', ['group_id' => $group->id, 'number' => 9]);
    }

    public function test_program_assignment_updates_student_education_and_group_processes(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'mfa_enabled' => true]);
        $this->actingAs($admin)->post(route('admin.programs.store'), ['name' => 'Media'])->assertRedirect()->assertSessionHasNoErrors();
        $program = EducationProgram::where('name', 'Media')->firstOrFail();
        $program->workProcesses()->create(['number' => 1, 'name' => 'Vormgeven']);
        $group = Group::create(['name' => 'M1']);
        $student = User::factory()->create(['role' => 'student']);
        $group->students()->attach($student);
        $this->patch(route('admin.groups.program', $group), ['education_program_id' => $program->id])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('group_work_processes', ['group_id' => $group->id, 'name' => 'Vormgeven']);
        $this->assertSame('Media', $student->fresh()->education);
        $student->update(['name' => 'Andere naam']);
        $this->assertSame('Media', $student->fresh()->education);
    }

    public function test_new_teacher_group_uses_admin_configured_default_processes(): void
    {
        $program = EducationProgram::where('name', 'Software Developer')->firstOrFail();
        $program->workProcesses()->where('number', 1)->update(['name' => 'Configuratie van admin']);
        $teacher = User::factory()->create(['role' => 'teacher', 'mfa_enabled' => true]);
        $this->actingAs($teacher)->post(route('teacher.groups.store'), ['name' => 'SD4A'])->assertRedirect()->assertSessionHasNoErrors();
        $group = Group::where('name', 'SD4A')->firstOrFail();
        $this->assertSame($program->id, $group->education_program_id);
        $this->assertDatabaseHas('group_work_processes', ['group_id' => $group->id, 'number' => 1, 'name' => 'Configuratie van admin']);
    }
}
