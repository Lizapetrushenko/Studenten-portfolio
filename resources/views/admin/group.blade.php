<x-app-layout headerTitle="Groep {{ $group->name }}">
    <div class="portal-shell space-y-6">
        <a href="{{ route('admin.dashboard') }}" class="teacher-link back-button">← Terug naar instellingen</a>
        @if ($errors->any())<div class="panel text-red-700">{{ $errors->first() }}</div>@endif

        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">GROEP BEHEREN</p><h2>{{ $group->name }}</h2></div><span class="count">{{ $group->students->count() }} studenten</span></div>
            @if($group->lifecycle_status)<x-group-status-photo :status="$group->lifecycle_status" size="h-40 w-40 mb-5" />@endif
            @if($group->lifecycle_status === null)
            <form method="POST" action="{{ route('admin.groups.update', $group) }}" enctype="multipart/form-data" class="form-grid">@csrf @method('PATCH')
                <label>Naam van de groep<input name="name" value="{{ old('name', $group->name) }}" maxlength="120" required></label>
                <label>Docent<select name="teacher_id"><option value="">Nog geen docent</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $group->teacher_id) === (string) $teacher->id)>{{ $teacher->name }}</option>@endforeach</select></label>
                <label>Groepsfoto<input type="file" name="photo" accept="image/*"><span class="mt-1 block text-sm text-gray-600">JPG, PNG of andere afbeelding, maximaal 5 MB.</span></label>
                @if($group->photo_path)<div class="flex items-center gap-4"> <img src="{{ asset('storage/'.$group->photo_path) }}" alt="Foto van {{ $group->name }}" class="h-24 w-24 rounded-full object-cover"><label class="flex-row items-center"><input type="checkbox" name="remove_photo" value="1"> Foto verwijderen</label></div>@endif
                <div class="full"><button class="button">Groepsgegevens opslaan</button></div>
            </form>
            @else
                <p>Dit is een vaste statusgroep. De naam en groep kunnen niet worden aangepast of verwijderd.</p>
            @endif
        </section>

        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">STUDENTEN</p><h2>Studenten in {{ $group->name }}</h2></div></div>
            <div class="overflow-x-auto"><table class="w-full text-left"><thead><tr><th class="p-3">Student</th><th class="p-3">Studentnummer</th><th class="p-3">E-mail</th><th class="p-3">Leerweg</th></tr></thead><tbody>
                @forelse($group->students as $student)<tr class="border-t"><td class="p-3"><a class="underline" href="{{ route('admin.students.show', $student) }}">{{ $student->displayName() }}</a></td><td class="p-3">{{ $student->student_number ?: '—' }}</td><td class="p-3">{{ $student->email }}</td><td class="p-3">{{ $student->study_type ?: '—' }}</td></tr>
                @empty<tr><td class="p-3" colspan="4">Er zitten nog geen studenten in deze groep.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </div>
</x-app-layout>
