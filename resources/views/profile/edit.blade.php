<x-app-layout headerTitle="Profiel">

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
            @if(!$user->isAdmin() && !($user->isStudent() && ($user->portfolioIsLocked() || $user->canOnlySubmitEvidence() || $user->portfolios()->whereHas('evidence', fn ($query) => $query->where('status', 'akkoord'))->exists())))
                @include('profile.partials.delete-user-form')
            @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
