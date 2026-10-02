<x-guest-layout>
    <x-slot:title>Wachtwoord vergeten?</x-slot:title>
    <x-slot:subtitle>Vul het e-mailadres van je account in.</x-slot:subtitle>
    <div class="mb-4 text-sm text-gray-600">
        Als je e-mailadres bestaat, kun je op de volgende pagina een nieuw wachtwoord kiezen.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-mailadres" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Verder naar nieuw wachtwoord
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
