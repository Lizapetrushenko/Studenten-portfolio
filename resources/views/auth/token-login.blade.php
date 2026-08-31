<x-guest-layout>
    <p class="deltion-kicker">TOKEN-AUTHENTICATIE</p>
    <h1 class="auth-title">Inloggen met token</h1>
    <p class="auth-subtitle">Maak eerst een token met je accountgegevens, of plak een bestaand token. Een token is 60 minuten geldig.</p>
    <form method="POST" action="{{ route('token.issue') }}" class="auth-form">@csrf
        <div class="auth-field"><label for="username">Gebruikersnaam of e-mailadres</label><input id="username" name="username" class="auth-input" required></div>
        <div class="auth-field"><label for="token-password">Wachtwoord</label><input id="token-password" name="password" type="password" class="auth-input" required></div>
        <button class="auth-submit" type="submit">Token maken</button>
    </form>
    @if(isset($token))<div class="auth-field token-result"><label for="generated-token">Jouw token</label><textarea id="generated-token" class="auth-input" rows="5" readonly>{{ $token }}</textarea><p>Bewaar dit token veilig.</p></div>@endif
    <form method="POST" action="{{ route('token.authenticate') }}" class="auth-form">@csrf
        <div class="auth-field"><label for="token">Token gebruiken</label><textarea id="token" name="token" class="auth-input" rows="5" required>{{ old('token', $token ?? '') }}</textarea><x-input-error :messages="$errors->get('token')" class="auth-error" /></div>
        <button class="auth-submit" type="submit">Inloggen met token</button>
    </form>
</x-guest-layout>