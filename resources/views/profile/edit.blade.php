<x-app-layout headerTitle="Profiel">
    @if($user->isStudent())
        <div class="student-profile-page py-8">
            <div class="student-profile-columns">
                <section class="panel student-profile-card">
                    <header><h2>Gegevens</h2></header>
                    <div class="student-profile-data-section profile-form-center">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                    <div class="student-profile-data-section">
                        <dl class="student-school-data">
                            @foreach($user->reviewers as $index => $reviewer)
                                <div><dt>Beoordelaar {{ $index + 1 }}:</dt><dd>{{ $reviewer->name }}</dd></div>
                            @endforeach
                            @if($user->reviewers->isEmpty())
                                <div><dt>Beoordelaars:</dt><dd>Nog niet gekoppeld</dd></div>
                            @endif
                            <div><dt>Groep:</dt><dd>{{ $user->groups->pluck('name')->join(', ') ?: 'Nog niet gekoppeld' }}</dd></div>
                            <div><dt>Opleidingstype:</dt><dd>{{ $user->study_type === 'Flex' ? 'FLEX' : ($user->study_type ?: 'Niet ingevuld') }}</dd></div>
                            <div><dt>Studentnummer:</dt><dd>{{ $user->student_number ?: 'Niet ingevuld' }}</dd></div>
                        </dl>
                    </div>
                </section>

                <section class="panel student-profile-card">
                    <div class="profile-form-center">
                        @include('profile.partials.update-password-form')
                    </div>
                </section>

                @if(!($user->portfolioIsLocked() || $user->canOnlySubmitEvidence() || $user->portfolios()->whereHas('evidence', fn ($query) => $query->where('status', 'akkoord'))->exists()))
                    <section class="panel student-profile-card">
                        <div class="profile-form-center">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </section>
                @endif
            </div>
        </div>
    @else
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-sm mx-auto profile-form-center">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-sm mx-auto profile-form-center">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                @if(!$user->isAdmin())
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-sm mx-auto profile-form-center">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
                @endif
            </div>
        </div>
    @endif
</x-app-layout>
