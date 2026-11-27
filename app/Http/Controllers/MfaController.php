<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use PragmaRX\Google2FAQRCode\QRCode\Bacon;
use PragmaRX\Google2FAQRCode\Google2FA;

class MfaController extends Controller
{
    public function setup(Request $request): View
    {
        if ($request->user()->mfa_enabled) {
            return view('auth.mfa-setup');
        }
        $secret = $request->user()->mfa_secret
            ? Crypt::decrypt($request->user()->mfa_secret)
            : ($request->session()->get('mfa_setup_secret') ?: $this->google2fa()->generateSecretKey());
        $request->session()->put('mfa_setup_secret', $secret);
        $google2fa = $this->google2fa();
        if (method_exists($google2fa, 'getQrCodeService') && ! $google2fa->getQrCodeService()) {
            $google2fa->setQrCodeService(new Bacon(new SvgImageBackEnd()));
        }
        $qrCode = $google2fa->getQRCodeInline(config('app.name'), $request->user()->email, $secret, 220);
        return view('auth.mfa-setup', compact('secret', 'qrCode'));
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $secret = $request->session()->get('mfa_setup_secret');
        if (! $secret || ! $this->google2fa()->verifyKey($secret, $request->string('code')->toString(), 1)) {
            return back()->withErrors(['code' => 'De MFA-code is onjuist.'])->withInput();
        }
        $request->user()->forceFill(['mfa_secret' => Crypt::encrypt($secret), 'mfa_enabled' => true])->save();
        $request->session()->forget('mfa_setup_secret');
        return redirect()->route('mfa.setup')->with('status', 'MFA is ingeschakeld.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'mfa_enabled' => false,
            'mfa_secret' => null,
        ])->save();
        $request->session()->forget('mfa_setup_secret');

        return redirect()->route('mfa.setup')->with('status', 'MFA is uitgeschakeld.');
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
        $valid = $user && $user->mfa_secret && $this->google2fa()->verifyKey(Crypt::decrypt($user->mfa_secret), $request->string('code')->toString(), 1);
        if (! $valid) return back()->withErrors(['code' => 'De MFA-code is onjuist.']);
        $request->session()->forget('mfa_user_id');
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    private function google2fa(): Google2FA
    {
        return app(Google2FA::class);
    }
}
