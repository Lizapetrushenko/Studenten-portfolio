<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TokenAuthController extends Controller
{
    public function show() { return view('auth.token-login'); }

    public function issue(Request $request)
    {
        $data = $request->validate(['username' => ['required', 'string'], 'password' => ['required', 'string']]);
        $user = User::where('username', $data['username'])->orWhere('email', $data['username'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) return back()->withErrors(['issue' => 'Ongeldige gebruikersnaam of wachtwoord.']);
        $now = time();
        $token = JWT::encode(['iss' => config('app.url'), 'sub' => $user->id, 'iat' => $now, 'exp' => $now + 3600], config('app.key'), 'HS256');
        return view('auth.token-login', compact('token'))->with('status', 'Token aangemaakt.');
    }

    public function login(Request $request)
    {
        $data = $request->validate(['username' => ['required', 'string'], 'password' => ['required', 'string']]);
        $user = User::where('username', $data['username'])->orWhere('email', $data['username'])->first();
        if (! $user || ! password_verify($data['password'], $user->password)) return response()->json(['message' => 'Ongeldige inloggegevens.'], 422);
        $now = time();
        $token = JWT::encode(['iss' => config('app.url'), 'sub' => $user->id, 'iat' => $now, 'exp' => $now + 3600], config('app.key'), 'HS256');
        return response()->json(['token' => $token, 'verloopt_over' => '60 minuten']);
    }

    public function authenticate(Request $request)
    {
        $request->validate(['token' => ['required', 'string']]);
        try { $payload = JWT::decode($request->string('token')->toString(), new Key(config('app.key'), 'HS256')); }
        catch (\Throwable) { return back()->withErrors(['token' => 'Token is ongeldig of verlopen.']); }
        $user = User::find((int) $payload->sub);
        if (! $user) return back()->withErrors(['token' => 'Token hoort bij geen bestaand account.']);
        if ($user->mfa_enabled) {
            $request->session()->put('mfa_user_id', $user->id);
            return redirect()->route('mfa.challenge');
        }
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('dashboard');
    }
}
