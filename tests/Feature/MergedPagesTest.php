<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MergedPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_has_one_profile_and_chart_and_accepts_busy_evidence(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'SD2B']);
        $group->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);
        $student = User::factory()->create(['role' => 'student', 'leerweg' => 'BBL', 'study_type' => 'BBL']);
        $student->groups()->sync([$group->id]);
        $this->actingAs($student)->get('/dashboard')->assertOk()->assertSee('Portfolio opslaan');
        $this->post(route('portfolio.store'), ['title' => 'Mijn portfolio'])->assertSessionHasNoErrors()->assertRedirect();
        $response = $this->get('/dashboard')->assertOk()->assertSee('value="bezig"', false);
        $this->assertSame(1, substr_count($response->getContent(), 'id="spinChart"'));
        $this->assertSame(1, substr_count($response->getContent(), '<h2>Portfolio-informatie</h2>'));
        $this->post(route('evidence.store'), ['title' => 'Mijn bewijs', 'work_process' => 1, 'category' => 'Schoolproject', 'status' => 'bezig', 'note' => 'Mijn notitie'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('evidence', ['title' => 'Mijn bewijs', 'status' => 'bezig', 'note' => 'Mijn notitie']);
    }

    public function test_teacher_pages_render_and_group_forms_are_not_nested(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'mfa_enabled' => true]);
        $group = $teacher->teachingGroups()->create(['name' => 'SD2B']);
        $group->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);
        $student = User::factory()->create(['role' => 'student', 'leerweg' => 'BBL']);
        $student->groups()->attach($group);
        $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BBL']);
        foreach (['teacher.dashboard', 'teacher.groups.index', 'teacher.students.index'] as $name) {
            $this->actingAs($teacher)->get(route($name))->assertOk();
        }
        foreach (['teacher.groups.show', 'teacher.groups.progress'] as $name) {
            $response = $this->get(route($name, $group))->assertOk();
            if ($name === 'teacher.groups.show') $this->assertFormsNotNested($response->getContent());
        }
        $this->get(route('teacher.students.show', $student))->assertOk();
        $this->get(route('teacher.students.portfolio', $student))->assertOk();
    }

    public function test_admin_pages_render_with_mfa_enabled(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'mfa_enabled' => true]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'SD2B']);
        $student = User::factory()->create(['role' => 'student']);
        $student->groups()->attach($group);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.teachers.show', $teacher))->assertOk();
        $this->get(route('admin.groups.show', $group))->assertOk();
        $this->get(route('admin.students.show', $student))->assertOk();
    }

    private function assertFormsNotNested(string $html): void
    {
        preg_match_all('/<\/?form\b[^>]*>/i', $html, $tags);
        $depth = 0;
        foreach ($tags[0] as $tag) {
            $depth += str_starts_with($tag, '</') ? -1 : 1;
            $this->assertTrue($depth >= 0 && $depth <= 1, 'Formulieren mogen niet genest zijn.');
        }
        $this->assertSame(0, $depth);
    }
}
