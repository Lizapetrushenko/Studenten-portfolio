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
                <fieldset class="full"><legend class="font-semibold mb-2">Statusgroep</legend><div class="grid gap-2 sm:grid-cols-2">@foreach($lifecycleGroups as $group)<label class="flex items-center gap-2"><input type="radio" name="lifecycle_group_id" value="{{ $group->id }}" @checked($student->groups->contains('id', $group->id)) required>{{ $group->name }}</label>@endforeach</div></fieldset>
                <fieldset class="full"><legend class="font-semibold mb-2">Mijn groepen</legend><div class="grid gap-2 sm:grid-cols-2">@forelse($teacherGroups as $group)<label class="flex items-center gap-2"><input type="checkbox" name="group_ids[]" value="{{ $group->id }}" @checked($student->groups->contains('id', $group->id))>{{ $group->name }}</label>@empty<p>Je hebt nog geen gewone groepen om aan deze student te koppelen.</p>@endforelse</div><p class="mt-2 text-sm text-gray-500">Een student kan lid zijn van meerdere groepen.</p></fieldset>
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
            <section class="panel"><p>Deze student staat in de groep WEG.</p><form method="POST" action="{{ route('teacher.students.destroy', $student) }}" onsubmit="return confirm('Deze student en het portfolio definitief verwijderen?')">@csrf @method('DELETE')<button class="delete-idea" type="submit">Student verwijderen</button></form></section>
        @endif
    </div>
</x-app-layout>
