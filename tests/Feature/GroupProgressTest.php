<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_progress_shows_evidence_and_filters_without_exposing_ideas(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'SD2B']);
        $group->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);
        $student = User::factory()->create(['name' => 'Anna Voorbeeld', 'role' => 'student']);
        $group->students()->attach($student);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BBL']);
        foreach (['Ingeleverd' => 'Plan document', 'Akkoord' => 'Akkoord document', 'Idee' => 'Prive idee', 'Bezig' => 'Concept document'] as $status => $title) {
            $portfolio->evidence()->create(['title' => $title, 'status' => $status, 'work_process' => '1', 'category' => 'Project', 'item_number' => 1]);
        }
        $url = route('teacher.groups.progress', $group);
        $this->actingAs($teacher)->get($url)->assertOk()->assertSee('K1W1')->assertSee('Plan document')->assertSee('Akkoord document')->assertDontSee('Prive idee')->assertDontSee('Concept document');
        $this->get($url.'?status=akkoord')->assertOk()->assertSee('Akkoord document')->assertDontSee('Plan document');
        $this->get($url.'?zoek=onbekend')->assertOk()->assertSee('Geen studenten gevonden met deze filters.')->assertDontSee('Plan document');
        $this->get($url.'?zoek=anna&status=ingeleverd')->assertOk()->assertSee('Plan document')->assertDontSee('Akkoord document');
        $this->get($url.'?status=Idee')->assertSessionHasErrors('status');
    }

    public function test_created_evidence_is_grey_and_turns_orange_after_student_submits(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'SD2B']);
        $group->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);
        $student = User::factory()->create(['role' => 'student']);
        $student->groups()->sync([$group->id]);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BBL']);
        $item = $portfolio->evidence()->create(['title' => 'Mijn bewijsstuk', 'status' => 'idee', 'work_process' => '1', 'category' => 'Project', 'item_number' => 1]);
        $url = route('teacher.groups.progress', $group);
        $this->actingAs($teacher)->get($url)->assertOk()->assertSee('class="progress-evidence progress-niet-ingeleverd"', false)->assertDontSee('Mijn bewijsstuk');
        $this->actingAs($student)->patch(route('evidence.update', $item), ['status' => 'bezig'])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($teacher)->get($url)->assertOk()->assertSee('class="progress-evidence progress-niet-ingeleverd"', false)->assertDontSee('Mijn bewijsstuk');
        $this->actingAs($student)->patch(route('evidence.update', $item), ['status' => 'ingeleverd'])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($teacher)->get($url)->assertOk()->assertSee('class="progress-evidence progress-ingeleverd"', false)->assertSee('Mijn bewijsstuk')->assertDontSee('class="progress-evidence progress-niet-ingeleverd"', false);
        $this->get($url.'?status=niet%20ingeleverd')->assertOk()->assertSee('Geen studenten gevonden met deze filters.');
    }
    public function test_only_linked_teachers_can_mark_submitted_evidence_for_review(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'SD2B']);
        $group->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);
        $student = User::factory()->create(['role' => 'student']);
        $group->students()->attach($student);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BBL']);
        $item = $portfolio->evidence()->create(['title' => 'Beoordelen', 'status' => 'ingeleverd', 'work_process' => '1', 'category' => 'Project', 'item_number' => 1, 'feedback' => 'Bestaande feedback']);
        $url = route('teacher.evidence.review', $item);
        $this->actingAs(User::factory()->create(['role' => 'teacher']))->patch($url, ['status' => 'nakijken'])->assertNotFound();
        $this->actingAs($student)->patch($url, ['status' => 'nakijken'])->assertForbidden();
        $this->actingAs($teacher)->patch($url, ['status' => 'nakijken'])->assertRedirect();
        $this->assertSame('nakijken', $item->fresh()->status);
        $this->assertSame('Bestaande feedback', $item->fresh()->feedback);
        $this->assertNull($item->fresh()->feedback_author_name);
        $this->patch($url, ['status' => 'nakijken', 'feedback' => 'Mijn nieuwe feedback'])->assertRedirect();
        $this->assertSame($teacher->displayName(), $item->fresh()->feedback_author_name);
        $this->get(route('teacher.students.portfolio', $student))->assertOk()->assertSee('Notitie van '.$teacher->displayName());
        $this->actingAs($student)->get('/dashboard')->assertOk()->assertSee('Notitie van '.$teacher->displayName());
        $this->actingAs($teacher)->patch($url, ['status' => 'nakijken'])->assertRedirect();
        $this->assertSame($teacher->displayName(), $item->fresh()->feedback_author_name);
        $this->get(route('teacher.groups.progress', $group).'?status=nakijken')->assertOk()->assertSee('Beoordelen');
        $this->patch($url, ['status' => 'akkoord'])->assertRedirect();
        $this->patch($url, ['status' => 'nakijken'])->assertStatus(422);
        $this->assertSame('akkoord', $item->fresh()->status);
    }
    public function test_progress_is_protected_by_login_role_and_group_ownership(): void
    {
        $owner = User::factory()->create(['role' => 'teacher']);
        $group = $owner->teachingGroups()->create(['name' => 'Eigen groep']);
        $url = route('teacher.groups.progress', $group);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'student']))->get($url)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'teacher']))->get($url)->assertNotFound();
    }

    public function test_lifecycle_group_uses_work_processes_from_students_assigned_groups(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $statusGroup = Group::where('lifecycle_status', 'herkansen')->firstOrFail();
        $group = $teacher->teachingGroups()->create(['name' => 'SD2B']);
        $group->workProcesses()->create(['number' => 6, 'name' => 'Projectteam']);
        $student = User::factory()->create(['role' => 'student']);
        $student->groups()->sync([$statusGroup->id, $group->id]);
        $this->actingAs($teacher)->get(route('teacher.groups.progress', $statusGroup))->assertOk()->assertSee('K2W1');
    }
}
