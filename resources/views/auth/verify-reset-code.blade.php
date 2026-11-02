<x-guest-layout>
    <x-slot:title>Controleer je e-mail</x-slot:title>
    <x-slot:subtitle>Vul de 6-cijferige code in die je per e-mail hebt ontvangen.</x-slot:subtitle>
    <x-auth-session-status class="mb-4" :status="$status ?? session('status')" />
    <form method="POST" action="{{ route('password.code.verify') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <x-input-label for="code" value="Verificatiecode" />
            <x-text-input id="code" class="auth-input" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="auth-error" />
        </div>
        <div class="auth-actions">
            <a class="forgot-link" href="{{ route('password.request') }}">Opnieuw beginnen</a>
            <x-primary-button class="auth-submit">Code controleren</x-primary-button>
        </div>
    </form>
</x-guest-layout>
