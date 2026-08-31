<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

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

    public static function callbackProvider(string $provider)
    {
        $providerUser = Socialite::driver($provider)->user();
        $user = User::updateOrCreate(
            ['email' => $providerUser->getEmail()],
            [
                'name' => $providerUser->getName() ?: $providerUser->getNickname() ?: 'Student',
                'username' => strtolower(Str::slug($providerUser->getNickname() ?: $providerUser->getEmail())) . '-' . substr(md5($providerUser->getEmail()), 0, 6),
                'password' => Hash::make(Str::random(48)),
                'email_verified_at' => now(),
            ]
        );

        if ($user->mfa_enabled) {
            session(['mfa_user_id' => $user->id]);
            return redirect()->route('mfa.challenge');
        }

        Auth::login($user, true);

        return redirect()->intended('/dashboard');
    }
}
