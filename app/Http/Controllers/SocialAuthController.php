<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use GuzzleHttp\Exception\GuzzleException;

class SocialAuthController extends Controller
{
    public function redirect()
    {
        return self::redirectProvider('google');
    }

    public static function redirectProvider(string $provider) { return Socialite::driver($provider)->redirect(); }

    public function callback()
    {
        return self::callbackProvider('google');
    }

    public function redirectToGithub()
    {
        return self::redirectProvider('github');
    }

    public function handleGithubCallback()
    {
        return self::callbackProvider('github');
    }

    public static function callbackProvider(string $provider)
    {
        if (request()->has('error')) {
            return redirect()->route('login')->with('status', 'Inloggen is geannuleerd. Probeer het opnieuw.');
        }

        try {
            $providerUser = Socialite::driver($provider)->user();
        } catch (InvalidStateException | GuzzleException $exception) {
            return redirect()->route('login')->with('status', 'Inloggen is niet gelukt. Start opnieuw vanaf deze pagina.');
        }

        if (! $providerUser->getEmail() || ($provider === 'google' && ! filter_var($providerUser->user['email_verified'] ?? $providerUser->user['verified_email'] ?? false, FILTER_VALIDATE_BOOLEAN))) {
            return redirect()->route('login')->with('status', 'Er is een bevestigd e-mailadres nodig om in te loggen.');
        }

        $user = User::firstOrCreate(
            ['email' => $providerUser->getEmail()],
            [
                'name' => $providerUser->getName() ?: $providerUser->getNickname() ?: 'Student',
                'username' => strtolower(Str::slug($providerUser->getNickname() ?: $providerUser->getEmail())) . '-' . substr(md5($providerUser->getEmail()), 0, 6),
                'password' => Hash::make(Str::random(48)),
            ]
        );

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        if ($user->mfa_enabled) {
            session(['mfa_user_id' => $user->id]);
            return redirect()->route('mfa.challenge');
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
}
