<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as ProviderUser;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_providers_redirect_with_the_configured_callback_and_state(): void
    {
        foreach (['google', 'github'] as $provider) {
            config(["services.$provider.client_id" => 'test-client', "services.$provider.client_secret" => 'test-secret']);
            $response = $this->get("/auth/$provider/redirect");
            $response->assertStatus(302);
            parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
            $this->assertSame(url("/auth/$provider/callback"), $query['redirect_uri']);
            $this->assertNotEmpty($query['state']);
        }
    }

    public function test_google_login_preserves_existing_password_and_profile(): void
    {
        $user = User::factory()->create(['username' => 'original']);
        $original = $user->only(['name', 'username', 'password']);
        $identity = (new ProviderUser)->setRaw(['email_verified' => true])->map([
            'id' => '123', 'email' => $user->email, 'name' => 'Changed name',
        ]);
        Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn($identity);
        $this->get('/auth/google/callback')->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertSame($original, $user->fresh()->only(['name', 'username', 'password']));
    }

    public function test_github_callback_creates_a_verified_account(): void
    {
        $identity = (new ProviderUser)->map([
            'id' => '123', 'email' => 'student@example.com', 'nickname' => 'student',
        ]);
        Socialite::shouldReceive('driver')->with('github')->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn($identity);
        $this->get('/auth/github/callback')->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertNotNull(User::where('email', 'student@example.com')->firstOrFail()->email_verified_at);
    }

    public function test_github_login_reuses_existing_account_without_changing_credentials(): void
    {
        $user = User::factory()->create(['username' => 'original']);
        $original = $user->only(['name', 'username', 'password']);
        $identity = (new ProviderUser)->map([
            'id' => '123', 'email' => $user->email, 'nickname' => 'different-name',
        ]);
        Socialite::shouldReceive('driver')->with('github')->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn($identity);

        $this->get('/auth/github/callback')->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($original, $user->fresh()->only(['name', 'username', 'password']));
    }

    public function test_github_without_a_verified_email_does_not_create_an_account(): void
    {
        $identity = (new ProviderUser)->map(['id' => '123', 'email' => null]);
        Socialite::shouldReceive('driver')->with('github')->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn($identity);

        $this->get('/auth/github/callback')->assertRedirect('/login')->assertSessionHas('status');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_invalid_state_returns_to_login(): void
    {
        Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
        Socialite::shouldReceive('user')->andThrow(new \Laravel\Socialite\Two\InvalidStateException);
        $this->get('/auth/google/callback')->assertRedirect('/login')->assertSessionHas('status');
        $this->assertGuest();
    }
}
