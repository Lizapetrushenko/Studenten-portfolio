<x-app-layout header-title="MFA instellen">
    <div class="portal-shell">
        <section class="panel auth-card">
            <p class="deltion-kicker">EXTRA BEVEILIGING</p>
            <h1 class="auth-title">MFA instellen</h1>
            @if (auth()->user()->mfa_enabled)
                <div role="status" aria-live="polite" style="margin: 1.25rem 0; padding: 1rem; border: 1px solid #15803d; border-radius: .5rem; background: #f0fdf4; color: #166534;">
                    <strong>MFA is ingeschakeld.</strong>
                    <p>Je account is beveiligd. Bij je volgende login vul je de code uit je authenticator-app in.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="auth-submit">Terug naar mijn portfolio</a>
            @else
            <p class="auth-subtitle">Scan deze QR-code met je authenticator-app en vul daarna de 6-cijferige code in.</p>
            @if ($errors->has('code'))
                <div role="alert" style="margin: 1rem 0; padding: 1rem; border: 1px solid #b91c1c; border-radius: .5rem; background: #fef2f2; color: #991b1b;">
                    {{ $errors->first('code') }}
                </div>
            @endif

            <div class="mfa-qr">{!! $qrCode !!}</div>
            <p class="mfa-help">Lukt scannen niet? Gebruik dan deze geheime sleutel:</p>
            <p class="mfa-secret">{{ $secret }}</p>

            <form method="POST" action="{{ route('mfa.enable') }}" class="auth-form">
                @csrf
                <div class="auth-field">
                    <label for="mfa-code">Authenticator-code</label>
                    <input id="mfa-code" class="auth-input" type="text" name="code"
                           inputmode="numeric" autocomplete="one-time-code"
                           pattern="[0-9]{6}" minlength="6" maxlength="6"
                           placeholder="123456" required
                           @error('code') aria-invalid="true" aria-describedby="mfa-code-error" @enderror>
                    <div id="mfa-code-error">
                        <x-input-error :messages="$errors->get('code')" class="auth-error" />
                    </div>
                </div>
                <button class="auth-submit" type="submit">MFA inschakelen</button>
            </form>
            @endif
        </section>
    </div>
</x-app-layout>
