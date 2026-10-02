<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
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
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $token = null;
        $status = Password::sendResetLink(
            $request->only('email'),
            function ($user, string $resetToken) use (&$token) {
                $token = $resetToken;
            }
        );

        return $status == Password::RESET_LINK_SENT
                    ? redirect()->route('password.reset', [
                        'token' => $token,
                        'email' => $request->input('email'),
                    ])
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
