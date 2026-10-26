<x-app-layout headerTitle="Studentgegevens · {{ $student->displayName() }}">
    <div class="portal-shell space-y-6 student-profile-page">
        <a href="{{ route('teacher.students.index') }}" class="teacher-link back-button">← Terug naar studentenoverzicht</a>
        @if (session('status'))<div class="panel" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="panel text-red-700">{{ $errors->first() }}</div>@endif

        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">STUDENTGEGEVENS · ALLEEN INZIEN</p><h2>{{ $student->displayName() }}</h2></div>@if($student->portfolios->isNotEmpty())<a class="button" href="{{ route('teacher.students.portfolio', $student) }}">Portfolio bekijken</a>@endif</div>
            <div class="form-grid">
                <label>Voornaam<input value="{{ $student->first_name }}" disabled></label>
                <label>Achternaam<input value="{{ $student->last_name }}" disabled></label>
                <label>Roepnaam<input value="{{ $student->preferred_name }}" disabled></label>
                <label>E-mailadres<input value="{{ $student->email }}" disabled></label>
                <label>Studentnummer<input value="{{ $student->student_number ?: 'Niet ingevuld' }}" disabled></label>
                <label>Opleiding<input value="{{ $student->education ?: 'Niet ingevuld' }}" disabled></label>
            </div>
        </section>

        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">BEHEER</p><h2>Groepen, beoordelaars en leerweg</h2></div></div>
            <form method="POST" action="{{ route('teacher.students.update', $student) }}" class="form-grid">@csrf @method('PATCH')
                @php
                    $selectedGroupIds = collect(old('group_ids', $student->groups->modelKeys()))->map(fn ($id) => (string) $id)->values();
                @endphp
                <fieldset class="full">
                    <legend class="font-semibold mb-2">Groepen</legend>
                    @if($groups->isNotEmpty())
                        <div class="student-group-select" x-data="{ open: false, selected: @js($selectedGroupIds) }" @click.outside="open = false" @keydown.escape="open = false">
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
                        </div>
                    @else
                        <p>Er zijn geen groepen beschikbaar om aan deze student te koppelen.</p>
                    @endif
                    <p class="mt-2 text-sm text-gray-500">Selecteer precies één statusgroep. Een student kan daarnaast lid zijn van meerdere groepen.</p>
                    <x-input-error :messages="$errors->get('group_ids')" />
                </fieldset>
                @php
                    $reviewerOptions = $teachers->map(fn ($teacher) => ['id' => (string) $teacher->id, 'name' => $teacher->name])->values()->all();
                    $selectedReviewerIds = collect(old('reviewer_ids', $student->reviewers->modelKeys()))->map(fn ($id) => (string) $id)->values()->all();
                @endphp
                <div class="reviewer-picker" x-data="{ open: false, search: '', options: @js($reviewerOptions), selected: @js($selectedReviewerIds) }">
                    <span class="reviewer-label" id="reviewer-label">Beoordelaars (minimaal 2)</span>
                    <details x-ref="reviewerDropdown" @toggle="open = $el.open; if (open) $nextTick(() => $refs.reviewerSearch.focus()); else search = ''" @click.outside="$el.open = false" @keydown.escape.prevent="$el.open = false; $refs.reviewerSummary.focus()">
                        <summary x-ref="reviewerSummary" aria-labelledby="reviewer-label reviewer-value">
                            <span id="reviewer-value" x-text="selected.length ? options.filter(option => selected.includes(option.id)).map(option => option.name).join(', ') : 'Kies beoordelaars'">{{ $teachers->whereIn('id', $selectedReviewerIds)->pluck('name')->join(', ') ?: 'Kies beoordelaars' }}</span>
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :style="{ transform: open ? 'rotate(180deg)' : 'rotate(0deg)' }"><path d="m6 9 6 6 6-6" /></svg>
                        </summary>
                        <div class="reviewer-menu">
                            <input x-ref="reviewerSearch" x-model="search" type="search" placeholder="Zoek een docent…" aria-label="Zoek beoordelaars op naam" autocomplete="off">
                            <div class="reviewer-options">
                                @foreach($teachers as $teacher)
                                    <label data-name="{{ $teacher->name }}" x-show="$el.dataset.name.toLocaleLowerCase().includes(search.trim().toLocaleLowerCase())">
                                        <input type="checkbox" name="reviewer_ids[]" value="{{ $teacher->id }}" x-model="selected" @checked(in_array((string) $teacher->id, $selectedReviewerIds, true))>
                                        <span>{{ $teacher->name }}</span>
                                    </label>
                                @endforeach
                                <p x-show="!options.some(option => option.name.toLocaleLowerCase().includes(search.trim().toLocaleLowerCase()))" x-cloak>Geen docenten gevonden.</p>
                            </div>
                        </div>
                    </details>
                    <p class="reviewer-hint" aria-live="polite" x-text="selected.length + ' geselecteerd · kies minimaal 2 beoordelaars'"></p>
                    <x-input-error :messages="$errors->get('reviewer_ids')" />
                </div>
                <label>Leerweg<span class="student-select-wrap"><select name="study_type" required><option value="">Kies leerweg</option><option value="BOL" @selected(strtoupper(old('study_type', $student->leerweg ?: $student->study_type) ?? '') === 'BOL')>BOL</option><option value="BBL" @selected(strtoupper(old('study_type', $student->leerweg ?: $student->study_type) ?? '') === 'BBL')>BBL</option><option value="Flex" @selected(strtoupper(old('study_type', $student->leerweg ?: $student->study_type) ?? '') === 'FLEX')>FLEX</option></select><svg class="student-select-arrow" aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg></span></label>
                <div class="full"><button class="button" type="submit">Gegevens opslaan</button></div>
            </form>
        </section>
        @if($student->hasLifecycleGroup('weg'))
            <section class="panel"><p>Deze student staat in de groep WEG.</p><form method="POST" action="{{ route('teacher.students.destroy', $student) }}" data-confirm="Weet u zeker dat u deze student en het portfolio definitief wilt verwijderen?">@csrf @method('DELETE')<button class="delete-idea" type="submit">Student verwijderen</button></form></section>
        @endif
    </div>
</x-app-layout>
