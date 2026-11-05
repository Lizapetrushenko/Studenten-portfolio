<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $originalEmail = $user->email;

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Test',
                'last_name' => 'User',
                'preferred_name' => 'Tes',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('Test', $user->first_name);
        $this->assertSame('User', $user->last_name);
        $this->assertSame('Tes', $user->preferred_name);
        $this->assertSame($originalEmail, $user->email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_student_profile_update_validates_required_names_and_length(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)->patch('/profile', [
            'first_name' => '', 'last_name' => str_repeat('x', 121),
        ])->assertSessionHasErrors(['first_name', 'last_name']);

        $this->assertSame($user->first_name, $user->fresh()->first_name);
    }

    public function test_admin_cannot_delete_their_account_through_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrorsIn('default', 'password');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_student_with_approved_evidence_or_locked_group_cannot_delete_account(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $portfolio = $student->portfolios()->create(['title' => 'Portfolio', 'study' => 'BOL']);
        $portfolio->evidence()->create([
            'title' => 'Akkoord', 'status' => 'akkoord', 'work_process' => '1', 'category' => 'Project', 'item_number' => 1,
        ]);

        $this->actingAs($student)->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', ['id' => $student->id]);

        $student->portfolios()->first()->evidence()->delete();
        $graduated = Group::where('lifecycle_status', 'geslaagd')->firstOrFail();
        $student->groups()->sync([$graduated->id]);
        $this->delete('/profile', ['password' => 'password'])->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', ['id' => $student->id]);
    }
}
