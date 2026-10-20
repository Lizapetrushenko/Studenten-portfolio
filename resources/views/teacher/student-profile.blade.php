<x-app-layout headerTitle="Studentgegevens · {{ $student->displayName() }}">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.students.index') }}" class="underline">← Terug naar studentenoverzicht</a>
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
            <div class="section-title"><div><p class="eyebrow">BEHEER</p><h2>Groepen, beoordelaars en opleidingstype</h2></div></div>
            <form method="POST" action="{{ route('teacher.students.update', $student) }}" class="form-grid">@csrf @method('PATCH')
                <fieldset class="full"><legend class="font-semibold mb-2">Statusgroep</legend><div class="grid gap-2 sm:grid-cols-2">@foreach($lifecycleGroups as $group)<label class="flex items-center gap-2"><input type="radio" name="lifecycle_group_id" value="{{ $group->id }}" @checked($student->groups->contains('id', $group->id)) required>{{ $group->name }}</label>@endforeach</div></fieldset>
                <fieldset class="full"><legend class="font-semibold mb-2">Mijn groepen</legend><div class="grid gap-2 sm:grid-cols-2">@forelse($teacherGroups as $group)<label class="flex items-center gap-2"><input type="checkbox" name="group_ids[]" value="{{ $group->id }}" @checked($student->groups->contains('id', $group->id))>{{ $group->name }}</label>@empty<p>Je hebt nog geen gewone groepen om aan deze student te koppelen.</p>@endforelse</div><p class="mt-2 text-sm text-gray-500">Een student kan lid zijn van meerdere groepen.</p></fieldset>
                <label>Beoordelaars <span>(minimaal 2)</span><select name="reviewer_ids[]" multiple size="5" required>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected($student->reviewers->contains('id', $teacher->id))>{{ $teacher->name }}</option>@endforeach</select></label>
                <label>Opleidingstype<select name="study_type" required><option value="">Kies type</option><option value="BOL" @selected($student->study_type === 'BOL')>BOL</option><option value="BBL" @selected($student->study_type === 'BBL')>BBL</option><option value="Flex" @selected($student->study_type === 'Flex')>Flex</option></select></label>
                <div class="full"><button class="button" type="submit">Gegevens opslaan</button></div>
            </form>
        </section>
        @if($student->hasLifecycleGroup('weg'))
            <section class="panel"><p>Deze student staat in de groep WEG.</p><form method="POST" action="{{ route('teacher.students.destroy', $student) }}" onsubmit="return confirm('Deze student en het portfolio definitief verwijderen?')">@csrf @method('DELETE')<button class="delete-idea" type="submit">Student verwijderen</button></form></section>
        @endif
    </div>
</x-app-layout>
