<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $emailPrefix = explode('@', mb_strtolower(trim((string) $request->input('email'))), 2)[0] ?? '';
        $request->merge(['student_number' => $emailPrefix]);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'preferred_name' => ['nullable', 'string', 'max:120'],
            'student_number' => ['required', 'string', 'max:40', 'unique:users,student_number'],
            'username' => ['nullable', 'alpha_dash', 'max:50', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'leerweg' => ['required', 'in:BOL,BBL,FLEX'],
        ], [
            'student_number.unique' => 'Het deel vóór @ van dit e-mailadres is al als studentnummer in gebruik.',
        ]);

        $name = trim($validated['first_name'].' '.$validated['last_name']);
        $user = User::create([
            'name' => $name,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'preferred_name' => ($validated['preferred_name'] ?? null) ?: $validated['first_name'],
            'student_number' => $validated['student_number'],
            'username' => ($validated['username'] ?? null) ?: strtolower(str_replace(' ', '-', $name)) . '-' . substr(md5($validated['email']), 0, 6),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'leerweg' => $validated['leerweg'],
            'study_type' => $validated['leerweg'] === 'FLEX' ? 'Flex' : $validated['leerweg'],
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
