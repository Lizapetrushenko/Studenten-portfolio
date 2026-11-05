<section>
    <header>
        @unless($user->isStudent())
            <h2 class="text-lg font-medium text-gray-900">Profielgegevens</h2>
        @endunless

        <p class="mt-1 text-sm text-gray-600">
            Werk je profielgegevens bij. Je e-mailadres kan niet worden gewijzigd.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        @if($user->isStudent())
            <div><x-input-label for="first_name" value="Voornaam" /><x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" required autocomplete="given-name" /><x-input-error class="mt-2" :messages="$errors->get('first_name')" /></div>
            <div><x-input-label for="last_name" value="Achternaam" /><x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" required autocomplete="family-name" /><x-input-error class="mt-2" :messages="$errors->get('last_name')" /></div>
            <div><x-input-label for="preferred_name" value="Roepnaam" /><x-text-input id="preferred_name" name="preferred_name" type="text" class="mt-1 block w-full" :value="old('preferred_name', $user->preferred_name)" autocomplete="nickname" /><x-input-error class="mt-2" :messages="$errors->get('preferred_name')" /></div>
        @else
            <div><x-input-label for="name" value="Naam" /><x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" /><x-input-error class="mt-2" :messages="$errors->get('name')" /></div>
        @endif

        <div>
            <x-input-label value="E-mailadres" />
            <p class="mt-1 text-sm text-gray-600">{{ $user->email }}</p>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        Je e-mailadres is nog niet bevestigd.

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Klik hier om de bevestigingsmail opnieuw te sturen.
                        </button>
                    </p>

                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Opslaan</x-primary-button>
        </div>

    </form>
</section>
