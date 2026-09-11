<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <!-- Email Address -->
        <div class="auth-field">
            <x-input-label for="login" value="Gebruikersnaam of e-mailadres" />
            <x-text-input id="login" class="auth-input" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('login')" class="auth-error" />
        </div>

        <!-- Password -->
        <div class="auth-field">
            <x-input-label for="password" value="Wachtwoord" />

            <x-text-input id="password" class="auth-input"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

       

        <div class="auth-actions">
            @if (Route::has('password.request'))
                <a class="forgot-link" href="{{ route('password.request') }}">
                    Wachtwoord vergeten?
                </a>
            @endif

            <x-primary-button class="auth-submit">
                Log in
            </x-primary-button>
        </div>
    </form>
    <div class="oauth-divider"><span>of</span></div>
<a class="oauth-button google-button" href="{{ route('google.redirect') }}">
    <img src="https://cdn.simpleicons.org/google" alt="Google">
    <span>Doorgaan met Google</span>
</a>

<a class="oauth-button github-button" href="{{ route('github.redirect') }}">
    <img src="https://cdn.simpleicons.org/github/ffffff" alt="GitHub">
    <span>Doorgaan met GitHub</span>
</a>

    <div class="register-container">
        <a class="register-link" href="{{ route('register') }}">Nog geen account?</a>
    </div>
</x-guest-layout>
