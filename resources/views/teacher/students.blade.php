<x-app-layout headerTitle="Studentenoverzicht">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.dashboard') }}" class="underline">← Terug naar docentendashboard</a>
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">DOCENT · OVERZICHT</p><h2>Alle studenten</h2></div><span class="count">{{ $students->count() }} studenten</span></div>
            <label class="block mb-5">Zoeken op naam, groep of beoordelaar<input id="student-search" type="search" placeholder="Typ om te zoeken…"></label>
            <div class="overflow-x-auto"><table class="w-full text-left" id="student-table"><thead><tr><th class="p-3">Naam</th><th class="p-3">Studentnummer</th><th class="p-3">Groepen</th><th class="p-3">Beoordelaars</th><th class="p-3">Opleidingstype</th><th class="p-3">Profiel</th></tr></thead><tbody>
                @forelse($students as $student)<tr class="border-t"><td class="p-3">{{ $student->displayName() }}</td><td class="p-3">{{ $student->student_number ?: '—' }}</td><td class="p-3">{{ $student->groups->pluck('name')->join(', ') ?: '—' }}</td><td class="p-3">{{ $student->reviewers->pluck('name')->join(', ') ?: 'Niet gekoppeld' }}</td><td class="p-3">{{ $student->study_type ?: '—' }}</td><td class="p-3"><a class="underline" href="{{ route('teacher.students.show', $student) }}">Student openen →</a></td></tr>
                @empty<tr><td class="p-3" colspan="6">Er zijn nog geen studenten aan jouw groepen of beoordeling gekoppeld.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </div>
    <script>
        document.getElementById('student-search')?.addEventListener('input', event => {
            const value = event.target.value.toLocaleLowerCase();
            document.querySelectorAll('#student-table tbody tr').forEach(row => row.hidden = !row.innerText.toLocaleLowerCase().includes(value));
        });
    </script>
</x-app-layout>
