<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Mail::shouldReceive('send')
            ->once()
            ->with(
                'emails.password-reset-code',
                Mockery::on(fn (array $data): bool => isset($data['code']) && preg_match('/^\d{6}$/', $data['code']) === 1),
                Mockery::type(\Closure::class),
            );

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertOk()->assertViewIs('auth.verify-reset-code');
        $this->assertSame($user->email, session('password_reset_email'));
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_reset_password_link_cannot_be_requested_for_an_unknown_email(): void
    {
        Mail::shouldReceive('send')->never();
        app()->setLocale('nl');

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'onbekend@example.com',
        ]);

        $response->assertOk()
            ->assertViewIs('auth.verify-reset-code')
            ->assertSee('Als dit e-mailadres bij een account hoort, is er een code verstuurd.');

        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Mail::shouldReceive('send')->once();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertOk()->assertViewIs('auth.verify-reset-code');
        $this->withSession(['password_reset_email' => $user->email])
            ->get(route('password.code'))
            ->assertOk()
            ->assertViewIs('auth.verify-reset-code')
            ->assertSee('name="code"', false);
    }

    public function test_password_can_be_reset_after_a_verified_code(): void
    {
        $user = User::factory()->create();
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);
        $this->withSession([
            'password_reset_email' => $user->email,
            'password_reset_verified_email' => $user->email,
            'password_reset_verified_at' => now()->timestamp,
        ]);

        $response = $this->post('/reset-password', [
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_valid_reset_code_opens_password_form_and_rejects_invalid_or_expired_codes(): void
    {
        $user = User::factory()->create();
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $this->withSession(['password_reset_email' => $user->email])
            ->post(route('password.code.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->post(route('password.code.verify'), ['code' => '123456'])
            ->assertRedirect(route('password.reset'))
            ->assertSessionHas('password_reset_verified_email', $user->email);
        $this->get(route('password.reset'))
            ->assertOk()
            ->assertViewIs('auth.reset-password')
            ->assertSee('Nieuw wachtwoord');

        DB::table('password_reset_tokens')->where('email', $user->email)->update(['created_at' => now()->subMinutes(11)]);
        $this->withSession(['password_reset_email' => $user->email])
            ->post(route('password.code.verify'), ['code' => '123456'])
            ->assertSessionHasErrors('code');
    }

    public function test_reset_code_route_requires_prior_email_submission(): void
    {
        $this->get(route('password.code'))->assertRedirect(route('password.request'));
        $this->get(route('password.reset'))->assertForbidden();
    }
}
