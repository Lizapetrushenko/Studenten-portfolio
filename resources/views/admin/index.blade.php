<x-app-layout headerTitle="Admin panel">
    <div class="portal-shell space-y-6" x-data="{ section: 'dashboard' }">
        @if (session('status'))<div class="panel" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="panel text-red-700">{{ $errors->first() }}</div>@endif

        <section class="panel" style="border: 1px solid var(--accent);" x-show="section === 'dashboard'">
            <div class="section-title"><div><p class="eyebrow">BEHEER</p><h2>Admin dashboard</h2></div></div>
            <div class="screen-grid">
                <button type="button" class="screen-card text-left" @click="section = 'teachers'"><span class="tag">{{ $teachers->count() }} accounts</span><h2>Docenten</h2><p>Docenten toevoegen en beheren.</p><strong>Openen →</strong></button>
                <button type="button" class="screen-card text-left" @click="section = 'students'"><span class="tag">{{ $students->count() }} accounts</span><h2>Studenten</h2><p>Studentgegevens inzien, groepen beheren en accounts verwijderen.</p><strong>Openen →</strong></button>
                <button type="button" class="screen-card text-left" @click="section = 'groups'"><span class="tag">{{ $groups->count() }} groepen</span><h2>Groepen</h2><p>De vaste statusgroepen en alle andere groepen bekijken.</p><strong>Openen →</strong></button>
            </div>
        </section>
        <nav class="flex flex-wrap gap-2" aria-label="Admin schermen" x-show="section !== 'dashboard'">
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
            <div class="overflow-x-auto"><table class="w-full text-left"><thead><tr><th class="p-3">Groep</th><th class="p-3">Docent</th><th class="p-3">Studenten</th></tr></thead><tbody>
                @forelse($groups as $group)<tr class="border-t"><td class="p-3 font-semibold">{{ $group->name }}</td><td class="p-3">{{ $group->teacher?->name ?? 'Nog niet toegewezen' }}</td><td class="p-3">{{ $group->students_count }}</td></tr>
                @empty<tr><td class="p-3" colspan="3">Er zijn nog geen groepen.</td></tr>@endforelse
            </tbody></table></div>
        </section>

        <section class="panel" style="border: 1px solid var(--accent);" x-show="section === 'teachers'" x-cloak>
            <div class="section-title"><div><p class="eyebrow">DOCENTEN</p><h2>Docentenlijst</h2></div><span class="count">{{ $teachers->count() }} docenten</span></div>
            <div class="overflow-x-auto"><table class="w-full text-left"><thead><tr><th class="p-3">Naam</th><th class="p-3">E-mail</th><th class="p-3">Groepen</th><th class="p-3">Actie</th></tr></thead><tbody>
                @forelse($teachers as $teacher)<tr class="border-t"><td class="p-3">{{ $teacher->name }}</td><td class="p-3">{{ $teacher->email }}</td><td class="p-3">{{ $teacher->teaching_groups_count }}</td><td class="p-3"><form method="POST" action="{{ route('admin.users.destroy', $teacher) }}" onsubmit="return confirm('Dit docentaccount verwijderen?')">@csrf @method('DELETE')<button class="text-red-700 underline">Verwijderen</button></form></td></tr>
                @empty<tr><td class="p-3" colspan="4">Er zijn nog geen docenten.</td></tr>@endforelse
            </tbody></table></div>
        </section>

        <section class="panel" style="border: 1px solid var(--accent);" x-show="section === 'students'" x-cloak>
            <div class="section-title"><div><p class="eyebrow">STUDENTEN</p><h2>Studentenlijst</h2></div><span class="count">{{ $students->count() }} studenten</span></div>
            <div class="overflow-x-auto"><table class="w-full text-left"><thead><tr><th class="p-3">Studentnummer</th><th class="p-3">Voornaam</th><th class="p-3">Achternaam</th><th class="p-3">Roepnaam</th><th class="p-3">E-mail</th><th class="p-3">Leerweg</th><th class="p-3">Groepen</th><th class="p-3">Beoordelaars</th><th class="p-3">Actie</th></tr></thead><tbody>
                @forelse($students as $student)<tr class="border-t align-top"><td class="p-3">{{ $student->student_number ?: '—' }}</td><td class="p-3">{{ $student->first_name ?: '—' }}</td><td class="p-3">{{ $student->last_name ?: '—' }}</td><td class="p-3">{{ $student->preferred_name ?: '—' }}</td><td class="p-3">{{ $student->email }}</td><td class="p-3">{{ $student->study_type ?: '—' }}</td><td class="p-3">{{ $student->groups->pluck('name')->join(', ') ?: '—' }}</td><td class="p-3">{{ $student->reviewers->pluck('name')->join(', ') ?: 'Nog niet gekoppeld' }}</td><td class="p-3"><form method="POST" action="{{ route('admin.students.group', $student) }}" class="space-y-2">@csrf @method('PATCH')<select name="group_ids[]" aria-label="Groepen voor {{ $student->name }}" multiple size="4" class="min-w-40">@foreach($groups as $group)<option value="{{ $group->id }}" @selected($student->groups->contains('id', $group->id))>{{ $group->name }}</option>@endforeach</select><button class="button tiny-button">Groepen opslaan</button></form><form class="mt-3" method="POST" action="{{ route('admin.users.destroy', $student) }}" onsubmit="return confirm('Dit studentaccount verwijderen?')">@csrf @method('DELETE')<button class="text-red-700 underline">Verwijderen</button></form></td></tr>
                @empty<tr><td class="p-3" colspan="9">Er zijn nog geen studenten.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </div>
</x-app-layout>
