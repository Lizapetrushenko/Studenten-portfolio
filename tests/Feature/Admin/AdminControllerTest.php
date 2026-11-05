<?php

namespace Tests\Feature\Admin;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_teacher_with_a_hashed_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.teachers.store'), [
            'name' => 'Docent Tester',
            'email' => 'docent@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $teacher = User::where('email', 'docent@example.com')->firstOrFail();
        $this->assertSame('teacher', $teacher->role);
        $this->assertTrue(Hash::check('Password123!', $teacher->password));
        $this->assertNotNull($teacher->email_verified_at);
    }

    public function test_admin_teacher_creation_validates_unique_email_and_password_confirmation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['email' => 'existing@example.com']);

        $this->actingAs($admin)->post(route('admin.teachers.store'), [
            'name' => 'Docent', 'email' => 'existing@example.com',
            'password' => 'Password123!', 'password_confirmation' => 'wrong',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_admin_can_create_group_and_eight_default_work_processes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($admin)->post(route('admin.groups.store'), [
            'name' => 'Software 2A', 'teacher_id' => $teacher->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $group = Group::where('name', 'Software 2A')->firstOrFail();
        $this->assertSame($teacher->id, $group->teacher_id);
        $this->assertCount(8, $group->workProcesses);
        $this->assertSame('Afstemmen en plannen', $group->workProcesses->first()->name);
    }

    public function test_admin_cannot_assign_a_student_as_group_teacher(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($admin)->post(route('admin.groups.store'), [
            'name' => 'Software 2A', 'teacher_id' => $student->id,
        ])->assertStatus(422);

        $this->assertDatabaseMissing('groups', ['name' => 'Software 2A']);
    }

    public function test_admin_can_update_group_and_remove_it_from_a_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $group = $teacher->teachingGroups()->create(['name' => 'Oud']);
        $student->groups()->sync([$group->id]);

        $this->actingAs($admin)->patch(route('admin.groups.update', $group), [
            'name' => 'Nieuw', 'teacher_id' => $admin->id,
        ])->assertStatus(422);
        $this->patch(route('admin.groups.update', $group), [
            'name' => 'Nieuw', 'teacher_id' => $teacher->id,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->patch(route('admin.students.group', $student), ['group_ids' => []])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Nieuw', $group->fresh()->name);
        $this->assertCount(0, $student->fresh()->groups);
        $this->assertNull($student->fresh()->group_id);
    }

    public function test_admin_cannot_assign_a_student_to_multiple_lifecycle_groups(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $lifecycleGroups = Group::whereIn('lifecycle_status', ['nieuw', 'herkansen'])->pluck('id');
        $this->assertCount(2, $lifecycleGroups);

        $this->actingAs($admin)->patch(route('admin.students.group', $student), [
            'group_ids' => $lifecycleGroups->all(),
        ])->assertStatus(422);
    }

    public function test_admin_cannot_delete_self_or_the_last_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertStatus(422);
        $this->delete(route('admin.users.destroy', $admin))->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_another_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $teacher))
            ->assertRedirect(route('admin.dashboard', ['section' => 'teachers']))
            ->assertSessionHas('status', 'Account verwijderd.');

        $this->assertDatabaseMissing('users', ['id' => $teacher->id]);
    }

    public function test_admin_cannot_edit_fixed_lifecycle_group_or_open_details_for_wrong_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $group = Group::where('lifecycle_status', 'nieuw')->firstOrFail();

        $this->actingAs($admin)->patch(route('admin.groups.update', $group), ['name' => 'Gewijzigd'])
            ->assertForbidden();
        $this->get(route('admin.teachers.show', $student))->assertNotFound();
        $this->get(route('admin.students.show', $admin))->assertNotFound();
        $this->assertDatabaseMissing('groups', ['id' => $group->id, 'name' => 'Gewijzigd']);
    }
}
