<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class MfaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_disable_mfa_and_see_confirmation(): void
    {
        $user = User::factory()->create([
            'mfa_enabled' => true,
            'mfa_secret' => Crypt::encrypt(app(Google2FA::class)->generateSecretKey()),
        ]);
        $this->actingAs($user)->get(route('mfa.setup'))
            ->assertSee('MFA uitschakelen')
            ->assertSee('Weet je zeker dat je MFA wilt uitschakelen?')
            ->assertSee('<dialog', false)
            ->assertSee('Annuleren')
            ->assertSee('Ja, MFA uitschakelen')
            ->assertDontSee('return confirm(', false);
        $this->assertTrue($user->fresh()->mfa_enabled);

        $this->withSession(['mfa_setup_secret' => 'old-secret'])
            ->post(route('mfa.disable'))
            ->assertRedirect(route('mfa.setup'))
            ->assertSessionHas('status', 'MFA is uitgeschakeld.')
            ->assertSessionMissing('mfa_setup_secret');

        $this->assertFalse($user->fresh()->mfa_enabled);
        $this->assertNull($user->fresh()->mfa_secret);
        $this->get(route('mfa.setup'))
            ->assertSee('MFA is uitgeschakeld.')
            ->assertSee('MFA instellen')
            ->assertDontSee('MFA uitschakelen');
    }

    public function test_guest_cannot_disable_mfa(): void
    {
        $this->post(route('mfa.disable'))->assertRedirect(route('login'));
    }
    public function test_logged_in_user_can_open_the_code_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('mfa.setup'))
            ->assertOk()
            ->assertSee('id="mfa-code"', false)
            ->assertSee('autocomplete="one-time-code"', false)
            ->assertSessionHas('mfa_setup_secret');
    }

    public function test_valid_code_enables_mfa_and_saves_the_secret(): void
    {
        $user = User::factory()->create();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $this->actingAs($user)
            ->withSession(['mfa_setup_secret' => $secret])
            ->from(route('mfa.setup'))
            ->post(route('mfa.enable'), ['code' => $google2fa->getCurrentOtp($secret)])
            ->assertRedirect(route('mfa.setup'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'MFA is ingeschakeld.')
            ->assertSessionMissing('mfa_setup_secret');

        $user->refresh();
        $this->assertTrue($user->mfa_enabled);
        $this->assertSame($secret, Crypt::decrypt($user->mfa_secret));
        for ($visit = 0; $visit < 2; $visit++) {
            $this->get(route('mfa.setup'))
                ->assertOk()
                ->assertSee('MFA is ingeschakeld.')
                ->assertSee('role="status"', false)
                ->assertDontSee('id="mfa-code"', false)
                ->assertDontSee($secret);
        }
    }

    public function test_invalid_code_shows_an_error_and_does_not_enable_mfa(): void
    {
        $user = User::factory()->create();
        $secret = app(Google2FA::class)->generateSecretKey();
        $google2fa = $this->mock(Google2FA::class);
        $google2fa->shouldReceive('verifyKey')->once()->with($secret, '123456', 1)->andReturn(false);

        $this->actingAs($user)
            ->withSession(['mfa_setup_secret' => $secret])
            ->from(route('mfa.setup'))
            ->post(route('mfa.enable'), ['code' => '123456'])
            ->assertRedirect(route('mfa.setup'))
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->mfa_enabled);
        $this->assertNull($user->fresh()->mfa_secret);
    }
}