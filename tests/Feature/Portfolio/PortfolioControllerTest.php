<?php

namespace Tests\Feature\Portfolio;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortfolioControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_and_update_portfolio(): void
    {
        $student = User::factory()->create(['role' => 'student', 'study_type' => 'BOL']);

        $this->actingAs($student)->post(route('portfolio.store'), [
            'title' => 'Portfolio', 'bio' => 'Eerste versie',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $portfolio = $student->portfolios()->firstOrFail();
        $this->post(route('portfolio.store'), [
            'title' => 'Bijgewerkt portfolio', 'bio' => 'Bijgewerkt',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Bijgewerkt portfolio', $portfolio->fresh()->title);
        $this->assertSame('Bijgewerkt', $portfolio->fresh()->bio);
    }

    public function test_student_can_create_evidence_only_for_a_work_process_in_their_group(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $group = $teacher->teachingGroups()->create(['name' => 'Klas A']);
        $group->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);
        $student = User::factory()->create(['role' => 'student']);
        $student->groups()->sync([$group->id]);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);

        $this->actingAs($student)->post(route('evidence.store'), [
            'title' => 'Plan', 'work_process' => 1, 'category' => 'Project', 'status' => 'ingeleverd',
            'note' => 'Mijn toelichting',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('evidence', [
            'portfolio_id' => $portfolio->id, 'title' => 'Plan', 'item_number' => 1,
            'status' => 'ingeleverd', 'note' => 'Mijn toelichting',
        ]);

        $this->postJson(route('evidence.store'), [
            'title' => 'Onbekend proces', 'work_process' => 2, 'category' => 'Project', 'status' => 'idee',
        ])->assertStatus(422);
    }

    public function test_student_can_edit_and_delete_unapproved_evidence_but_cannot_edit_another_students(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        $item = $portfolio->evidence()->create([
            'title' => 'Bewijs', 'status' => 'ingeleverd', 'work_process' => '1',
            'category' => 'Project', 'item_number' => 1,
        ]);

        $this->actingAs($student)->patch(route('evidence.update', $item), [
            'title' => 'Gewijzigd', 'status' => 'ingeleverd', 'note' => 'Nieuwe notitie',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Gewijzigd', $item->fresh()->title);

        $this->actingAs($otherStudent)->patch(route('evidence.update', $item), [
            'title' => 'Niet van mij', 'status' => 'ingeleverd',
        ])->assertForbidden();

        $this->actingAs($student)->delete(route('evidence.destroy', $item))->assertRedirect();
        $this->assertDatabaseMissing('evidence', ['id' => $item->id]);
    }

    public function test_approved_evidence_cannot_be_deleted_and_approved_portfolio_cannot_be_removed(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        $approved = $portfolio->evidence()->create([
            'title' => 'Akkoord bewijs', 'status' => 'akkoord', 'work_process' => '1',
            'category' => 'Project', 'item_number' => 1,
        ]);

        $this->actingAs($student)->delete(route('evidence.destroy', $approved))->assertStatus(422);
        $this->delete(route('portfolio.destroy', $portfolio))->assertStatus(422);

        $this->assertDatabaseHas('evidence', ['id' => $approved->id]);
        $this->assertDatabaseHas('portfolios', ['id' => $portfolio->id]);
    }

    public function test_student_can_remove_evidence_file_and_download_owned_file(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $student = User::factory()->create(['role' => 'student']);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        $path = UploadedFile::fake()->create('bewijs.pdf', 10, 'application/pdf')->store('evidence', 'local');
        $item = $portfolio->evidence()->create([
            'title' => 'Bewijs', 'status' => 'ingeleverd', 'work_process' => '1', 'category' => 'Project',
            'item_number' => 1, 'file_path' => $path, 'original_file_name' => 'bewijs.pdf',
        ]);

        $this->actingAs($student)->get(route('evidence.download', $item))->assertOk();
        $this->delete(route('evidence.file.destroy', $item))->assertRedirect();
        Storage::disk('local')->assertMissing($path);
        $this->assertNull($item->fresh()->file_path);
        $this->assertNull($item->fresh()->original_file_name);
    }

    public function test_locked_student_cannot_mutate_portfolio_or_existing_evidence(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        $item = $portfolio->evidence()->create([
            'title' => 'Bewijs', 'status' => 'ingeleverd', 'work_process' => '1', 'category' => 'Project', 'item_number' => 1,
        ]);
        $graduatedGroup = Group::where('lifecycle_status', 'geslaagd')->firstOrFail();
        $student->groups()->sync([$graduatedGroup->id]);

        $this->actingAs($student)->post(route('portfolio.store'), ['title' => 'Gewijzigd'])
            ->assertForbidden();
        $this->patch(route('evidence.update', $item), ['status' => 'ingeleverd'])
            ->assertForbidden();
        $this->assertSame('Portfolio', $portfolio->fresh()->title);
        $this->assertSame('ingeleverd', $item->fresh()->status);
    }

    public function test_retake_student_can_submit_new_evidence_but_cannot_edit_existing_evidence(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $regularGroup = $teacher->teachingGroups()->create(['name' => 'Klas']);
        $regularGroup->workProcesses()->create(['number' => 1, 'name' => 'Plannen']);
        $student = User::factory()->create(['role' => 'student']);
        $retakeGroup = Group::where('lifecycle_status', 'herkansen')->firstOrFail();
        $student->groups()->sync([$regularGroup->id, $retakeGroup->id]);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        $existing = $portfolio->evidence()->create([
            'title' => 'Bestaand', 'status' => 'ingeleverd', 'work_process' => '1', 'category' => 'Project', 'item_number' => 1,
        ]);

        $this->actingAs($student)->post(route('evidence.store'), [
            'title' => 'Nieuw bewijs', 'work_process' => 1, 'category' => 'Project', 'status' => 'ingeleverd',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->patch(route('evidence.update', $existing), ['title' => 'Aanpassen', 'status' => 'ingeleverd'])
            ->assertForbidden();

        $this->assertDatabaseHas('evidence', ['portfolio_id' => $portfolio->id, 'title' => 'Nieuw bewijs']);
        $this->assertSame('Bestaand', $existing->fresh()->title);
    }

    public function test_student_can_clear_an_idea_and_delete_an_unapproved_portfolio(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        $idea = $portfolio->evidence()->create([
            'title' => 'Idee', 'idea' => 'Een plan', 'status' => 'idee', 'work_process' => '1',
            'category' => 'Project', 'item_number' => 1,
        ]);
        $submitted = $portfolio->evidence()->create([
            'title' => 'Ingeleverd', 'status' => 'ingeleverd', 'work_process' => '1',
            'category' => 'Project', 'item_number' => 2,
        ]);

        $this->actingAs($student)->delete(route('evidence.idea.destroy', $idea))->assertRedirect();
        $this->assertNull($idea->fresh()->idea);

        $this->delete(route('portfolio.destroy', $portfolio))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status');
        $this->assertDatabaseMissing('portfolios', ['id' => $portfolio->id]);
        $this->assertDatabaseMissing('evidence', ['id' => $idea->id]);
        $this->assertDatabaseMissing('evidence', ['id' => $submitted->id]);
    }
}
