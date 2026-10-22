<x-app-layout headerTitle="Studentenoverzicht">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.dashboard') }}" class="underline">← Terug naar docentendashboard</a>
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">DOCENT · OVERZICHT</p><h2>Lijst van studenten</h2></div><span class="count">{{ $students->count() }} studenten</span></div>
            <div x-data="{ search: '', group: '', reviewer: '' }">
                <div class="student-list-filters">
                    <label>Zoeken op naam<input type="search" x-model="search" placeholder="Zoeken op naam"></label>
                    <label>Filter op groep<select x-model="group"><option value="">Alle groepen</option>@foreach($students->flatMap->groups->unique('id')->sortBy('name') as $group)<option value="{{ $group->id }}">{{ $group->name }}</option>@endforeach</select></label>
                    <label>Filter op beoordelaar<select x-model="reviewer"><option value="">Alle beoordelaars</option>@foreach($students->flatMap->reviewers->unique('id')->sortBy('name') as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select></label>
                </div>
                <div class="overflow-x-auto"><table class="student-list-table"><thead><tr><th>Naam</th><th>Groepen</th><th>Beoordelaars</th><th>Profiel</th></tr></thead><tbody>
                    @forelse($students as $student)<tr x-show="(!search || @js(mb_strtolower($student->displayName().' '.$student->email.' '.$student->student_number)).includes(search.toLocaleLowerCase())) && (!group || @js($student->groups->pluck('id')->map(fn ($id) => (string) $id)->all()).includes(group)) && (!reviewer || @js($student->reviewers->pluck('id')->map(fn ($id) => (string) $id)->all()).includes(reviewer))"><td><strong>{{ $student->displayName() }}</strong><small>{{ $student->email }} · {{ $student->student_number ?: 'Geen studentnummer' }} · {{ $student->study_type ?: 'Geen opleidingstype' }}</small></td><td>{{ $student->groups->pluck('name')->join(', ') ?: 'Nog geen groep' }}</td><td>{{ $student->reviewers->pluck('name')->join(', ') ?: 'Nog niet gekoppeld' }}</td><td><a class="underline" href="{{ route('teacher.students.show', $student) }}">Student openen →</a></td></tr>
                    @empty<tr><td colspan="4">Er zijn nog geen studenten aan jouw groepen of beoordeling gekoppeld.</td></tr>@endforelse
                </tbody></table></div>
                <p class="mt-4" x-show="(search || group || reviewer) && ![...$el.parentElement.querySelectorAll('tbody tr')].some(row => row.style.display !== 'none')" x-cloak>Geen studenten gevonden met deze filters.</p>
            </div>
        </section>
    </div>
</x-app-layout>
