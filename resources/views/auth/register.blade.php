<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Naam" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- <div class="mt-4">
            <x-input-label for="username" value="Gebruikersnaam" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div> -->

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" value="E-mailadres" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Wachtwoord" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Wachtwoord bevestigen" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <style>
            #leerweg + svg {
                transition: transform 180ms ease;
            }

            #leerweg:open + svg {
                transform: translateY(-50%) rotate(180deg) !important;
            }

            @media (prefers-reduced-motion: reduce) {
                #leerweg + svg {
                    transition: none;
                }
            }
        </style>

        <!-- Leerweg -->
        <div class="mt-4">
            <x-input-label for="leerweg" value="Leerweg" />
            <div class="relative mt-1">
                <select id="leerweg" name="leerweg" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" style="appearance: none; background-image: none; padding-right: 2.5rem;" required>
                    <option value="" disabled @selected(! old('leerweg'))>Kies je leerweg</option>
                    <option value="BOL" @selected(old('leerweg') === 'BOL')>BOL</option>
                    <option value="BBL" @selected(old('leerweg') === 'BBL')>BBL</option>
                    <option value="FLEX" @selected(old('leerweg') === 'FLEX')>FLEX</option>
                </select>
                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); pointer-events: none;">
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </div>
            <x-input-error :messages="$errors->get('leerweg')" class="mt-2" />
        </div>
        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                Al een account?
            </a>
        
            <x-primary-button class="ms-4">
                Registreren
            </x-primary-button>
        </div>
    </form>

    <div class="oauth-divider"><span>of</span></div>
    <div class="oauth-buttons">
<a class="oauth-button google-button" href="{{ route('google.redirect') }}">
    <img src="{{ asset('images/google.svg') }}" alt="" width="20" height="20">
    <span>Doorgaan met Google</span>
</a>

<a class="oauth-button github-button" href="{{ route('github.redirect') }}">
    <img src="https://cdn.simpleicons.org/github/202124" alt="" width="20" height="20">
    <span>Doorgaan met GitHub</span>
</a>
</div>
</x-guest-layout>
