<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.portal-header', ['headerTitle' => $headerTitle])

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        <div x-data="{ showConfirmation: false, confirmationMessage: '', pendingForm: null }" @app-confirm.window="pendingForm = $event.detail.form; confirmationMessage = $event.detail.message; showConfirmation = true; $nextTick(() => $refs.confirmCancel.focus())">
            <div x-cloak x-show="showConfirmation" x-transition.opacity class="email-confirmation-backdrop" role="presentation" @click.self="showConfirmation = false">
                <div class="email-confirmation-dialog" role="dialog" aria-modal="true" aria-labelledby="app-confirmation-title" @keydown.escape.window="showConfirmation = false" @click.stop>
                    <h3 id="app-confirmation-title">Weet u het zeker?</h3>
                    <p x-text="confirmationMessage"></p>
                    <div class="email-confirmation-actions">
                        <button x-ref="confirmCancel" type="button" class="secondary-button" @click="showConfirmation = false">Annuleren</button>
                        <button type="button" class="button" @click="const form = pendingForm; showConfirmation = false; pendingForm = null; form.dataset.confirmed = 'true'; form.requestSubmit()">Bevestigen</button>
                    </div>
                </div>
            </div>
        </div>
        @if (session('status'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.opacity role="status" aria-live="polite" class="success-toast">
                <span class="success-toast-icon" aria-hidden="true">✓</span>
                <div class="success-toast-message">
                    <strong>Gelukt</strong>
                    <span>{{ match(session('status')) { 'profile-updated' => 'Profielgegevens succesvol gewijzigd.', 'password-updated' => 'Wachtwoord succesvol gewijzigd.', 'verification-link-sent' => 'Er is een nieuwe bevestigingslink verstuurd.', default => session('status') } }}</span>
                </div>
                <button type="button" class="success-toast-close" aria-label="Melding sluiten" @click="show = false">×</button>
            </div>
        @endif
    </body>
</html>
