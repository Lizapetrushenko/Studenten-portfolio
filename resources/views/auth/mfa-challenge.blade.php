<x-guest-layout>
    <p class="deltion-kicker">EXTRA BEVEILIGING</p>
    <h1 class="auth-title">Bevestig je login</h1>
    <p class="auth-subtitle">Vul de 6-cijferige code uit Google Authenticator in.</p>
    <form method="POST" action="{{ route('mfa.verify') }}" class="auth-form">@csrf
        <div class="auth-field"><label for="code">MFA-code</label><input id="code" name="code" class="auth-input" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus><x-input-error :messages="$errors->get('code')" class="auth-error" /></div>
        <button class="auth-submit" type="submit">Code controleren</button>
    </form>
</x-guest-layout>