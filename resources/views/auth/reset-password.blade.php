<x-guest-layout>
    <x-slot:title>Nieuw wachtwoord</x-slot:title>
    <x-slot:subtitle>Kies een nieuw wachtwoord en bevestig het hieronder.</x-slot:subtitle>
    <form method="POST" action="{{ route('password.store') }}" class="auth-form">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="auth-field">
            <x-input-label for="email" value="E-mailadres" />
            <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email', $request->email)" required readonly autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <!-- Password -->
        <div class="auth-field">
            <x-input-label for="password" value="Nieuw wachtwoord" />
            <x-text-input id="password" class="auth-input" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <!-- Confirm Password -->
        <div class="auth-field">
            <x-input-label for="password_confirmation" value="Bevestig nieuw wachtwoord" />

            <x-text-input id="password_confirmation" class="auth-input"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
        </div>

        <div class="auth-actions">
            <a class="forgot-link" href="{{ route('password.request') }}">Terug naar e-mailadres</a>
            <x-primary-button class="auth-submit">
                Wachtwoord wijzigen
            </x-primary-button>
        </div>
    </form>
    <a class="register-link" href="{{ route('login') }}">Terug naar inloggen</a>
</x-guest-layout>
