<x-app-layout headerTitle="Docentgegevens">
    <div class="portal-shell space-y-6">
        <a href="{{ route('admin.dashboard', ['section' => 'teachers']) }}" class="teacher-link back-button">← Terug naar docentenlijst</a>
        <section class="panel student-details-panel" style="border: 1px solid var(--accent);">
            <div class="section-title"><div><p class="eyebrow">DOCENTGEGEVENS</p><h2>{{ $teacher->name }}</h2></div></div>
            <div class="student-detail-grid">
                <label>Naam<input value="{{ $teacher->name }}" disabled></label>
                <label>Voornaam<input value="{{ $firstName ?: 'Niet ingevuld' }}" disabled></label>
                <label>Achternaam<input value="{{ $lastName ?: 'Niet ingevuld' }}" disabled></label>
                <label>E-mail<input value="{{ $teacher->email }}" disabled></label>
                <label>Groepen<input value="{{ $teacher->teachingGroups->pluck('name')->join(', ') ?: 'Nog geen groepen' }}" disabled></label>
            </div>
            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <form method="POST" action="{{ route('admin.users.destroy', $teacher) }}" data-confirm="Weet u zeker dat u docent {{ $teacher->name }} wilt verwijderen?">@csrf @method('DELETE')<x-danger-button type="submit">Docent verwijderen</x-danger-button></form>
            </div>
        </section>
    </div>
</x-app-layout>
