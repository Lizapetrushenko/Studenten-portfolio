<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless($this->hasVerifiedCode($request), 403);

        return view('auth.reset-password', ['request' => $request]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->hasVerifiedCode($request), 403);

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->session()->get('password_reset_verified_email');
        $user = User::where('email', $email)->firstOrFail();
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $request->session()->forget(['password_reset_email', 'password_reset_verified_email', 'password_reset_verified_at']);
        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', __('passwords.reset'));
    }

    private function hasVerifiedCode(Request $request): bool
    {
        $email = $request->session()->get('password_reset_verified_email');
        $verifiedAt = $request->session()->get('password_reset_verified_at');

        return $email && $verifiedAt && now()->timestamp - $verifiedAt <= 600
            && DB::table('password_reset_tokens')->where('email', $email)->exists();
    }
}
