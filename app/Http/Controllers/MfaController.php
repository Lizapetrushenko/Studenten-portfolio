<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use PragmaRX\Google2FAQRCode\Google2FA;

class MfaController extends Controller
{
    public function setup(Request $request): View
    {
        $secret = $request->user()->mfa_secret
            ? Crypt::decrypt($request->user()->mfa_secret)
            : ($request->session()->get('mfa_setup_secret') ?: app(Google2FA::class)->generateSecretKey());
        $request->session()->put('mfa_setup_secret', $secret);
        $qrCode = app(Google2FA::class)->getQRCodeInline(config('app.name'), $request->user()->email, $secret, 220);
        return view('auth.mfa-setup', compact('secret', 'qrCode'));
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $secret = $request->session()->get('mfa_setup_secret');
        if (! $secret || ! app(Google2FA::class)->verifyKey($secret, $request->string('code')->toString(), 1)) {
            return back()->withErrors(['code' => 'De MFA-code is onjuist.'])->withInput();
        }
        $request->user()->update(['mfa_secret' => Crypt::encrypt($secret), 'mfa_enabled' => true]);
        $request->session()->forget('mfa_setup_secret');
        return back()->with('status', 'MFA is ingeschakeld.');
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('mfa_user_id')) return redirect()->route('login');

        return view('auth.mfa-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $user = \App\Models\User::find($request->session()->get('mfa_user_id'));
        $valid = $user && $user->mfa_secret && app(Google2FA::class)->verifyKey(Crypt::decrypt($user->mfa_secret), $request->string('code')->toString(), 1);
        if (! $valid) return back()->withErrors(['code' => 'De MFA-code is onjuist.']);
        $request->session()->forget('mfa_user_id');
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }
}
