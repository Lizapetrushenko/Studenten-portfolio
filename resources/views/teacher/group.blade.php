<x-app-layout headerTitle="Groep {{ $group->name }}">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.dashboard') }}" class="underline">← Terug naar mijn groepen</a>
        @if(!$group->lifecycle_status)<a href="{{ route('teacher.groups.settings', $group) }}" class="button inline-flex">Instellingen werkprocessen</a>@endif
        @if ($errors->any())<div class="panel text-red-700">{{ $errors->first() }}</div>@endif
        <section class="panel">
            @if($group->lifecycle_status)<x-group-status-photo :status="$group->lifecycle_status" size="h-40 w-40 mb-5" />@endif
            <div class="section-title"><div><p class="eyebrow">GROEP</p><h2>{{ $group->name }}</h2></div><span class="count">{{ $group->students->count() }} studenten</span></div>
            @if(!$group->lifecycle_status)
                <form method="POST" action="{{ route('teacher.groups.update', $group) }}" enctype="multipart/form-data" class="form-grid mb-6 border-b pb-5">@csrf @method('PATCH')
                    <label>Naam van de groep<input name="name" value="{{ $group->name }}" maxlength="120" required></label>
                    <label>Groepsfoto<input type="file" name="photo" accept="image/*"><span class="mt-1 block text-sm text-gray-600">Afbeelding, maximaal 5 MB.</span></label>
                    @if($group->photo_path)<div class="flex items-center gap-4"><img src="{{ asset('storage/'.$group->photo_path) }}" alt="Foto van {{ $group->name }}" class="h-20 w-20 rounded-full object-cover"><label class="flex-row items-center"><input type="checkbox" name="remove_photo" value="1"> Foto verwijderen</label></div>@endif
                    <div class="full"><button class="button">Groepsgegevens opslaan</button></div>
                </form>
            @endif
            @if(!$group->lifecycle_status)<form method="POST" action="{{ route('teacher.groups.students.store', $group) }}" class="form-grid">@csrf
                <label>Student aan deze groep toevoegen<select name="student_id" required><option value="">Kies een student</option>@foreach($availableStudents as $student)<option value="{{ $student->id }}">{{ $student->displayName() }} — {{ $student->email }}</option>@endforeach</select></label>
                <div class="full"><button class="button" @disabled($availableStudents->isEmpty())>Toevoegen aan groep</button></div>
                @if($availableStudents->isEmpty())<p class="full">Alle studenten zijn al aan een groep gekoppeld.</p>@endif
            </form>@else<p>Dit is een vaste statusgroep. Studenten komen hierin via hun statusinstelling.</p>@endif
        </section>
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">STUDENTEN</p><h2>Portfolio's in deze groep</h2></div></div>
            <div class="overflow-x-auto"><table class="w-full text-left"><thead><tr><th class="p-3">Student</th><th class="p-3">Portfolio</th>@foreach($group->workProcesses as $process)<th class="p-3 text-center">{{ $process->number <= 5 ? 'K1W'.$process->number : 'K2W'.($process->number - 5) }}</th>@endforeach<th class="p-3">Actie</th></tr></thead><tbody>
                @forelse($group->students as $student)@php($portfolio = $student->portfolios->first())<tr class="border-t"><td class="p-3"><a class="underline" href="{{ route('teacher.students.show', $student) }}">{{ $student->displayName() }}</a></td><td class="p-3">{{ $portfolio?->title ?? 'Nog niet aangemaakt' }}</td>@foreach($group->workProcesses as $process)@php($items = $portfolio?->evidence->where('work_process', (string) $process->number)->whereIn('status', ['ingeleverd', 'akkoord', 'niet akkoord']) ?? collect())@php($cellClass = $items->contains('status', 'niet akkoord') ? 'text-red-700' : ($items->contains('status', 'akkoord') ? 'text-green-700' : 'text-orange-600'))<td class="p-3 text-center font-semibold {{ $cellClass }}">{{ $items->count() }}</td>@endforeach<td class="p-3">@if($portfolio)<a class="underline" href="{{ route('teacher.students.portfolio', $student) }}">Portfolio beoordelen</a>@else<span class="text-gray-500">Nog geen portfolio</span>@endif</td></tr>
                @empty<tr><td colspan="{{ 3 + $group->workProcesses->count() }}" class="p-3">Er zitten nog geen studenten in deze groep.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </div>
</x-app-layout>
