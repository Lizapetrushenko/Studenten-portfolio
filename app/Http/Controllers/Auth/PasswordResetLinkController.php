<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): View
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = Str::lower($request->string('email')->trim()->toString());
        $request->session()->forget(['password_reset_email', 'password_reset_verified_email', 'password_reset_verified_at']);
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user) {
            $code = (string) random_int(100000, 999999);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($code), 'created_at' => now()]
            );

            Mail::send('emails.password-reset-code', [
                'code' => $code,
                'name' => $user->name,
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)->subject('Code voor het wijzigen van je wachtwoord');
            });
        }

        $request->session()->put('password_reset_email', $user?->email ?? $email);
        return view('auth.verify-reset-code', [
            'status' => 'Als dit e-mailadres bij een account hoort, is er een code verstuurd.',
        ]);
    }

    public function showCodeForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_email')) {
            return redirect()->route('password.request');
        }
        return view('auth.verify-reset-code');
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $email = $request->session()->get('password_reset_email');
        $reset = $email ? DB::table('password_reset_tokens')->where('email', $email)->first() : null;

        $expiresAt = $reset?->created_at
            ? \Illuminate\Support\Carbon::parse($reset->created_at)->addMinutes(10)
            : null;

        if (! $reset || ! $expiresAt || now()->isAfter($expiresAt) || ! Hash::check($request->input('code'), $reset->token)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'code' => 'De code klopt niet of is verlopen. Vraag een nieuwe code aan.',
            ]);
        }

        $request->session()->put('password_reset_verified_email', $email);
        $request->session()->put('password_reset_verified_at', now()->timestamp);
        return redirect()->route('password.reset');
    }
}
