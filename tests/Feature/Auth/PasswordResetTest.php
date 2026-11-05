<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
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
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHasNoErrors();
        $this->assertStringContainsString('/reset-password/', $response->headers->get('Location'));
        Notification::assertNothingSent();
    }

    public function test_reset_password_link_cannot_be_requested_for_an_unknown_email(): void
    {
        Notification::fake();
        app()->setLocale('nl');

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'onbekend@example.com',
        ]);

        $response->assertRedirect('/forgot-password')
            ->assertSessionHasErrors(['email' => 'Dit e-mailadres bestaat niet.']);

        Notification::assertNothingSent();
        $this->get('/forgot-password')->assertSee('Dit e-mailadres bestaat niet.');
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $this->get($response->headers->get('Location'))
            ->assertStatus(200)
            ->assertSee('Nieuw wachtwoord')
            ->assertSee('Bevestig nieuw wachtwoord')
            ->assertSee($user->email);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $redirect = $this->post('/forgot-password', ['email' => $user->email]);
        $token = basename(parse_url($redirect->headers->get('Location'), PHP_URL_PATH));

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
