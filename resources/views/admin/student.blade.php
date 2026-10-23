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
                <form class="student-group-select" method="POST" action="{{ route('admin.students.group', $student) }}" x-data="{ open: false, selected: @js($student->groups->pluck('id')->map(fn ($id) => (string) $id)->values()) }" @click.outside="open = false" @keydown.escape="open = false">
                    @csrf @method('PATCH')
                    <span class="student-group-label">Groepen</span>
                    <div class="group-multiselect">
                        <div class="group-multiselect-value" role="group" aria-label="Geselecteerde groepen">
                            <div class="group-multiselect-chips">
                                @foreach($groups as $group)
                                    <span class="group-multiselect-chip" x-cloak x-show="selected.includes('{{ $group->id }}')">
                                        {{ $group->name }}
                                        <button type="button" aria-label="{{ $group->name }} verwijderen" @click="selected = selected.filter(id => id !== '{{ $group->id }}')">×</button>
                                    </span>
                                @endforeach
                                <span class="group-multiselect-placeholder" x-cloak x-show="selected.length === 0">Kies groepen</span>
                            </div>
                            <button type="button" class="group-multiselect-toggle" aria-label="Groepen selecteren" :aria-expanded="open.toString()" @click="open = !open">
                                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :style="{ transform: open ? 'rotate(180deg)' : 'rotate(0deg)' }"><path d="m6 9 6 6 6-6" /></svg>
                            </button>
                        </div>
                        <div class="group-multiselect-menu" x-cloak x-show="open" x-transition.opacity>
                            @foreach($groups as $group)
                                <label class="group-multiselect-option" :class="selected.includes('{{ $group->id }}') ? 'is-selected' : ''">
                                    <input type="checkbox" name="group_ids[]" value="{{ $group->id }}" x-model="selected" @checked($student->groups->contains('id', $group->id))>
                                    <span>{{ $group->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <button class="button tiny-button" type="submit">Groepen opslaan</button>
                </form>
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
