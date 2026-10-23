<x-app-layout headerTitle="Admin panel">
    <div class="portal-shell space-y-6" x-data="{ section: ['dashboard', 'teachers', 'students', 'groups'].includes(new URLSearchParams(window.location.search).get('section')) ? new URLSearchParams(window.location.search).get('section') : 'dashboard' }" x-init="$watch('section', value => { const url = new URL(window.location.href); if (value === 'dashboard') url.searchParams.delete('section'); else url.searchParams.set('section', value); window.history.replaceState({}, '', url); })">
        @if ($errors->any())<div class="panel text-red-700">{{ $errors->first() }}</div>@endif

        <section class="panel admin-dashboard-panel" style="border: 1px solid var(--accent);" x-show="section === 'dashboard'">
            <div class="section-title"><div><p class="eyebrow">BEHEER</p><h2>Admin dashboard</h2></div></div>
            <div class="screen-grid">
                <button type="button" class="screen-card text-left" @click="section = 'teachers'"><span class="tag">{{ $teachers->count() }} accounts</span><h2>Docenten</h2><p>Docenten toevoegen en beheren.</p><strong>Openen →</strong></button>
                <button type="button" class="screen-card text-left" @click="section = 'students'"><span class="tag">{{ $students->count() }} accounts</span><h2>Studenten</h2><p>Studentgegevens inzien, groepen beheren en accounts verwijderen.</p><strong>Openen →</strong></button>
                <button type="button" class="screen-card text-left" @click="section = 'groups'"><span class="tag">{{ $groups->count() }} groepen</span><h2>Groepen</h2><p>De vaste statusgroepen en alle andere groepen bekijken.</p><strong>Openen →</strong></button>
            </div>
        </section>
        <nav class="flex flex-wrap gap-2" aria-label="Admin schermen" x-show="section !== 'dashboard'" x-cloak>
            <button class="button tiny-button" type="button" @click="section = 'dashboard'">← Dashboard</button>
            <button class="button tiny-button" type="button" @click="section = 'teachers'">Docenten</button>
            <button class="button tiny-button" type="button" @click="section = 'students'">Studenten</button>
            <button class="button tiny-button" type="button" @click="section = 'groups'">Groepen</button>
        </nav>

        <section class="panel" style="border: 1px solid var(--accent);" x-show="section === 'teachers'" x-cloak>
            <div class="section-title"><div><p class="eyebrow">BEHEER</p><h2>Docent toevoegen</h2></div></div>
            <form method="POST" action="{{ route('admin.teachers.store') }}" class="form-grid">@csrf
                <label>Naam<input name="name" value="{{ old('name') }}" required></label>
                <label>E-mailadres<input type="email" name="email" value="{{ old('email') }}" required></label>
                <label>Tijdelijk wachtwoord<input type="password" name="password" required></label>
                <label>Herhaal wachtwoord<input type="password" name="password_confirmation" required></label>
                <div class="full"><button class="button">Docent aanmaken</button></div>
            </form>
        </section>

        <section class="panel" style="border: 1px solid var(--accent);" x-show="section === 'groups'" x-cloak>
            <div class="section-title"><div><p class="eyebrow">BEHEER</p><h2>Groep aanmaken</h2></div></div>
            <form method="POST" action="{{ route('admin.groups.store') }}" class="form-grid">@csrf
                <label>Naam van de groep<input name="name" value="{{ old('name') }}" placeholder="Bijvoorbeeld BOL 2A" required></label>
                <div x-data="{ open: false, selectedName: @js($teachers->firstWhere('id', old('teacher_id'))?->name ?? 'Nog geen docent') }">
                    <span id="group-teacher-label">Docent</span>
                    <details x-ref="teacherDropdown" @toggle="open = $el.open" @click.outside="$el.open = false" @keydown.escape.prevent="$el.open = false; $el.querySelector('summary').focus()" style="position: relative; margin-top: .35rem;">
                        <summary aria-labelledby="group-teacher-label group-teacher-value" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; cursor: pointer; list-style: none; padding: .75rem .85rem; border: 1px solid var(--line); border-radius: 5px; background: #fff; color: var(--ink); font-size: .9rem;">
                            <span id="group-teacher-value" x-text="selectedName">{{ $teachers->firstWhere('id', old('teacher_id'))?->name ?? 'Nog geen docent' }}</span>
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :style="{ transform: open ? 'rotate(180deg)' : 'rotate(0deg)' }" style="flex-shrink: 0; transition: transform .2s ease;"><path d="m6 9 6 6 6-6" /></svg>
                        </summary>
                        <div style="position: absolute; z-index: 20; inset-inline: 0; top: calc(100% + .3rem); max-height: 15rem; overflow-y: auto; padding: .25rem; border: 1px solid var(--line); border-radius: 5px; background: #fff; color: var(--ink); font-size: .9rem;">
                            <label style="display: flex; align-items: center; gap: .5rem; padding: .5rem .6rem; cursor: pointer; font-size: .9rem; font-weight: 400; color: var(--ink);"><input type="radio" name="teacher_id" value="" @checked(! old('teacher_id')) @change="selectedName = $el.parentElement.textContent.trim(); $refs.teacherDropdown.open = false" style="width: 16px; height: 16px; flex: 0 0 16px; padding: 0; margin: 0; accent-color: var(--ink);">Nog geen docent</label>
                            @foreach($teachers as $teacher)
                                <label style="display: flex; align-items: center; gap: .5rem; padding: .5rem .6rem; cursor: pointer; font-size: .9rem; font-weight: 400; color: var(--ink);"><input type="radio" name="teacher_id" value="{{ $teacher->id }}" @checked((string) old('teacher_id') === (string) $teacher->id) @change="selectedName = $el.parentElement.textContent.trim(); $refs.teacherDropdown.open = false" style="width: 16px; height: 16px; flex: 0 0 16px; padding: 0; margin: 0; accent-color: var(--ink);">{{ $teacher->name }}</label>
                            @endforeach
                        </div>
                    </details>
                    <x-input-error :messages="$errors->get('teacher_id')" class="mt-2" />
                </div>
                <div class="full"><button class="button">Groep aanmaken</button></div>
            </form>
        </section>

        <section class="panel" style="border: 1px solid var(--accent);" x-show="section === 'groups'" x-cloak>
            <div class="section-title"><div><p class="eyebrow">GROEPEN</p><h2>Alle groepen</h2></div><span class="count">{{ $groups->count() }} groepen</span></div>
            <div x-data="{ search: '' }">
                <label class="mb-5 block">Zoeken op groepsnaam<input type="search" x-model="search" placeholder="Zoeken op naam" aria-label="Zoeken op groepsnaam"></label>
                <div class="screen-grid">
                    @forelse($groups as $group)
                        <a x-show="!search || @js(mb_strtolower($group->name)).includes(search.toLocaleLowerCase())" class="screen-card text-center" href="{{ route('admin.groups.show', $group) }}">
                            @if($group->lifecycle_status)<x-group-status-photo :status="$group->lifecycle_status" size="h-36 w-36 mb-3" />@elseif($group->photo_path)<img src="{{ asset('storage/'.$group->photo_path) }}" alt="Foto van {{ $group->name }}" class="mx-auto mb-3 h-36 w-36 rounded-full object-cover">@else<div class="group-photo-placeholder mx-auto mb-3" aria-hidden="true"></div>@endif
                            <span class="tag">{{ $group->students_count }} studenten</span><h2>{{ $group->name }}</h2><p>{{ $group->teacher?->name ?? 'Nog geen docent toegewezen' }}</p><strong>Groep openen →</strong>
                        </a>
                    @empty<p>Er zijn nog geen groepen.</p>@endforelse
                </div>
                <p class="mt-4" x-show="search && ![...$el.parentElement.querySelectorAll('a.screen-card')].some(card => card.style.display !== 'none')" x-cloak>Geen groepen gevonden.</p>
            </div>
        </section>

        <section class="panel" style="border: 1px solid var(--accent);" x-show="section === 'teachers'" x-cloak>
            <div class="section-title"><div><p class="eyebrow">DOCENTEN</p><h2>Docentenlijst</h2></div><span class="count">{{ $teachers->count() }} docenten</span></div>
            <div class="overflow-x-auto"><table class="w-full text-left"><thead><tr><th class="p-3">Naam</th><th class="p-3">E-mail</th><th class="p-3">Groepen</th></tr></thead><tbody>
                @forelse($teachers as $teacher)<tr class="border-t"><td class="p-3"><a class="underline" href="{{ route('admin.teachers.show', $teacher) }}">{{ $teacher->name }}</a></td><td class="p-3">{{ $teacher->email }}</td><td class="p-3">{{ $teacher->teaching_groups_count }}</td></tr>
                @empty<tr><td class="p-3" colspan="3">Er zijn nog geen docenten.</td></tr>@endforelse
            </tbody></table></div>
        </section>

        <section class="panel" style="border: 1px solid var(--accent);" x-show="section === 'students'" x-cloak>
            <div class="section-title"><div><p class="eyebrow">BEHEER</p><h2>Lijst van studenten</h2></div><span class="count">{{ $students->count() }} studenten</span></div>
            <div x-data="{ search: '', group: '', reviewer: '' }">
                <div class="student-list-filters">
                    <label>Zoeken op naam<input type="search" x-model="search" placeholder="Zoeken op naam"></label>
                    <label>Filter op groep<select x-model="group"><option value="">Alle groepen</option>@foreach($groups as $group)<option value="{{ $group->id }}">{{ $group->name }}</option>@endforeach</select></label>
                    <label>Filter op beoordelaar<select x-model="reviewer"><option value="">Alle beoordelaars</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select></label>
                </div>
                <div class="overflow-x-auto"><table class="student-list-table"><thead><tr><th>Naam</th><th>Groepen</th><th>Beoordelaars</th><th>Beheer</th></tr></thead><tbody>
                    @forelse($students as $student)
                        <tr x-show="(!search || @js(mb_strtolower($student->displayName().' '.$student->email.' '.$student->student_number)).includes(search.toLocaleLowerCase())) && (!group || @js($student->groups->pluck('id')->map(fn ($id) => (string) $id)->all()).includes(group)) && (!reviewer || @js($student->reviewers->pluck('id')->map(fn ($id) => (string) $id)->all()).includes(reviewer))">
                            <td><strong><a class="underline" href="{{ route('admin.students.show', $student) }}">{{ $student->displayName() }}</a></strong><small>{{ $student->email }} · {{ $student->student_number ?: 'Geen studentnummer' }} · {{ $student->study_type ?: 'Geen opleidingstype' }}</small></td>
                            <td>{{ $student->groups->pluck('name')->join(', ') ?: 'Nog geen groep' }}</td>
                            <td>{{ $student->reviewers->pluck('name')->join(', ') ?: 'Nog niet gekoppeld' }}</td>
                            <td><details><summary class="cursor-pointer underline">Groepen wijzigen</summary><form method="POST" action="{{ route('admin.students.group', $student) }}" class="mt-3 space-y-2">@csrf @method('PATCH')<select name="group_ids[]" aria-label="Groepen voor {{ $student->name }}" multiple size="4" class="min-w-40">@foreach($groups as $group)<option value="{{ $group->id }}" @selected($student->groups->contains('id', $group->id))>{{ $group->name }}</option>@endforeach</select><button class="button tiny-button">Opslaan</button></form></details></td>
                        </tr>
                    @empty<tr><td colspan="4">Er zijn nog geen studenten.</td></tr>@endforelse
                </tbody></table></div>
                <p class="mt-4" x-show="(search || group || reviewer) && ![...$el.parentElement.querySelectorAll('tbody tr')].some(row => row.style.display !== 'none')" x-cloak>Geen studenten gevonden met deze filters.</p>
            </div>
        </section>
    </div>
</x-app-layout>
