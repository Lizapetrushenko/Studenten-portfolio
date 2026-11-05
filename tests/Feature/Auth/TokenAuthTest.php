<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TokenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_login_page_is_rendered(): void
    {
        $this->get(route('token.login'))->assertOk()->assertSee('Inloggen met token');
    }

    public function test_api_endpoint_issues_a_signed_jwt_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'username' => 'student1', 'password' => Hash::make('secret-password'),
        ]);

        $response = $this->postJson(route('token.api.login'), [
            'username' => 'student1', 'password' => 'secret-password',
        ])->assertOk()->assertJsonPath('verloopt_over', '60 minuten');

        $payload = JWT::decode($response->json('token'), new Key(config('app.key'), 'HS256'));
        $this->assertSame($user->id, $payload->sub);
        $this->assertSame(config('app.url'), $payload->iss);
    }

    public function test_api_endpoint_rejects_invalid_credentials_and_missing_fields(): void
    {
        $this->postJson(route('token.api.login'), [
            'username' => 'missing', 'password' => 'wrong',
        ])->assertStatus(422)->assertJsonPath('message', 'Ongeldige e-mail of wachtwoord.');

        $this->postJson(route('token.api.login'), [])->assertUnprocessable()->assertJsonValidationErrors(['username', 'password']);
    }

    public function test_valid_token_authenticates_user_and_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = JWT::encode([
            'iss' => config('app.url'), 'sub' => $user->id, 'iat' => time(), 'exp' => time() + 3600,
        ], config('app.key'), 'HS256');

        $this->post(route('token.authenticate'), ['token' => $token])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        auth()->logout();
        $this->post(route('token.authenticate'), ['token' => 'not-a-token'])
            ->assertSessionHasErrors('token');
        $this->assertGuest();
    }

    public function test_token_authentication_sends_mfa_users_to_the_challenge_first(): void
    {
        $user = User::factory()->create(['mfa_enabled' => true]);
        $token = JWT::encode([
            'iss' => config('app.url'), 'sub' => $user->id, 'iat' => time(), 'exp' => time() + 3600,
        ], config('app.key'), 'HS256');

        $this->post(route('token.authenticate'), ['token' => $token])
            ->assertRedirect(route('mfa.challenge'))
            ->assertSessionHas('mfa_user_id', $user->id);
        $this->assertGuest();
    }
}
