<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_authenticated_and_role_restricted_routes(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('teacher.dashboard'))->assertRedirect(route('login'));
    }

    public function test_only_admins_can_open_admin_routes_and_only_teachers_can_open_teacher_routes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($teacher)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('teacher.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('teacher.dashboard'))->assertForbidden();
    }

    public function test_dashboard_dispatches_each_role_to_the_matching_home_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($teacher)->get(route('dashboard'))->assertRedirect(route('teacher.dashboard'));
        $this->actingAs($student)->get(route('dashboard'))->assertOk();
    }

    public function test_unverified_user_cannot_enter_verified_dashboard(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'student']);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
    }
}
