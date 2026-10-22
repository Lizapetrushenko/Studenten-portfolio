<x-app-layout headerTitle="Studentgegevens">
    <div class="portal-shell space-y-6">
        <a href="{{ route('admin.dashboard', ['section' => 'students']) }}" class="underline">← Terug naar studentenlijst</a>
        <section class="panel student-details-panel" style="border: 1px solid var(--accent);" x-data="{ showDeleteConfirmation: false }" @keydown.escape.window="showDeleteConfirmation = false">
            <div class="section-title"><div><p class="eyebrow">STUDENTGEGEVENS</p><h2>{{ $student->displayName() }}</h2></div></div>
            <div class="student-detail-grid">
                <label>E-mail<input value="{{ $student->email }}" disabled></label>
                <label>Voornaam<input value="{{ $student->first_name ?: 'Niet ingevuld' }}" disabled></label>
                <label>Achternaam<input value="{{ $student->last_name ?: 'Niet ingevuld' }}" disabled></label>
                <label>Roepnaam<input value="{{ $student->preferred_name ?: 'Niet ingevuld' }}" disabled></label>
                <label>Studentnummer<input value="{{ $student->student_number ?: 'Niet ingevuld' }}" disabled></label>
                <label>Opleidingstype<input value="{{ $student->study_type === 'Flex' ? 'FLEX' : ($student->study_type ?: 'Niet ingevuld') }}" disabled></label>
                <label>Groepen<input value="{{ $student->groups->pluck('name')->join(', ') ?: 'Nog geen groep' }}" disabled></label>
                <label>Beoordelaars<input value="{{ $student->reviewers->pluck('name')->join(', ') ?: 'Nog niet gekoppeld' }}" disabled></label>
            </div>
            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <x-danger-button type="button" @click="showDeleteConfirmation = true">Student verwijderen</x-danger-button>
            </div>
            <div x-cloak x-show="showDeleteConfirmation" x-transition.opacity class="email-confirmation-backdrop" role="presentation" @click.self="showDeleteConfirmation = false">
                <div class="email-confirmation-dialog" role="dialog" aria-modal="true" aria-labelledby="delete-student-title" @click.stop>
                    <h3 id="delete-student-title">Student verwijderen?</h3>
                    <p>Weet u zeker dat u student {{ $student->displayName() }} wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.</p>
                    <div class="email-confirmation-actions">
                        <button type="button" class="secondary-button" @click="showDeleteConfirmation = false">Annuleren</button>
                        <form method="POST" action="{{ route('admin.users.destroy', $student) }}">@csrf @method('DELETE')<x-danger-button type="submit">Bevestigen</x-danger-button></form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
