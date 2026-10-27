<x-app-layout headerTitle="Instellingen">
    <div class="portal-shell space-y-6">
        <nav class="teacher-page-nav"><a class="teacher-link back-button" href="{{ route('admin.dashboard') }}">← Terug naar instellingen</a></nav>
        @if(session('status'))<div class="panel" role="status">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="panel text-red-700" role="alert">{{ $errors->first() }}</div>@endif
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">01 / KWALIFICATIEDOSSIER</p><h2>KD en opleiding instellen</h2></div><span class="count">{{ $programs->count() }} opleidingen</span></div>
            <form method="GET" action="{{ route('admin.settings') }}" class="form-grid">
                <label>Opleiding / KD<select name="program" onchange="this.form.submit()">@foreach($programs as $option)<option value="{{ $option->id }}" @selected($option->id === $program->id)>{{ $option->name }}</option>@endforeach</select></label>
                <div class="flex items-end"><button class="button" type="submit">Opleiding openen</button></div>
            </form>
            <p class="mt-4">Software Developer is automatisch beschikbaar als standaardopleiding. Beheer hieronder de werkprocessen die studenten in hun portfolio zien.</p>
            <details class="mt-6 border-t pt-5"><summary class="cursor-pointer font-semibold">Opleiding toevoegen</summary>
                <form method="POST" action="{{ route('admin.programs.store') }}" class="form-grid mt-4">@csrf<label>Naam van de opleiding / KD<input name="name" maxlength="120" value="{{ old('name') }}" required placeholder="Naam van de opleiding"></label><div class="flex items-end"><button class="button">Opleiding toevoegen</button></div></form>
            </details>
            @if($program->name !== 'Software Developer')
                <form method="POST" action="{{ route('admin.programs.update', $program) }}" class="form-grid mt-6 border-t pt-5">@csrf @method('PATCH')<label>Naam van de opleiding<input name="name" value="{{ $program->name }}" required maxlength="120"></label><div class="flex items-end"><button class="button">Naam opslaan</button></div></form>
            @endif
        </section>
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">02 / WERKPROCESSEN</p><h2>{{ $program->name }}</h2></div><span class="count">{{ $program->workProcesses->count() }} werkprocessen</span></div>
            <p class="mb-5">Wijzigingen gelden voor alle groepen die aan deze opleiding zijn gekoppeld.</p>
            @forelse($program->workProcesses as $process)
                <div class="flex flex-wrap items-end gap-3 border-t py-4">
                    <form method="POST" action="{{ route('admin.processes.update', [$program, $process]) }}" class="work-process-edit-row flex-1">@csrf @method('PATCH')
                        <p class="eyebrow">{{ $process->number <= 5 ? 'K1W'.$process->number : 'K2W'.($process->number - 5) }}</p>
                        <label>Naam van werkproces<input name="name" value="{{ $process->name }}" maxlength="120" required></label><button class="button" type="submit">Opslaan</button>
                    </form>
                    <form method="POST" action="{{ route('admin.processes.destroy', [$program, $process]) }}" data-confirm="Dit werkproces verwijderen uit de opleiding en de gekoppelde groepen?">@csrf @method('DELETE')<button class="button secondary-button" type="submit">Verwijderen</button></form>
                </div>
            @empty<p class="mb-4">Voeg het eerste werkproces voor deze opleiding toe.</p>@endforelse
            <form method="POST" action="{{ route('admin.processes.store', $program) }}" class="form-grid border-t pt-5">@csrf<label>Nieuw werkproces<input name="name" maxlength="120" required placeholder="Naam van het werkproces"></label><div class="flex items-end"><button class="button">Werkproces toevoegen</button></div></form>
        </section>
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">03 / GROEPEN</p><h2>Opleiding per groep</h2></div></div>
            @forelse($groups as $group)
                <form method="POST" action="{{ route('admin.groups.program', $group) }}" class="form-grid border-t py-4">@csrf @method('PATCH')
                    <label>{{ $group->name }} · {{ $group->students_count }} studenten<select name="education_program_id">@foreach($programs as $option)<option value="{{ $option->id }}" @selected($option->id === $group->education_program_id)>{{ $option->name }}</option>@endforeach</select></label><div class="flex items-end"><button class="button">Opleiding opslaan</button></div>
                </form>
            @empty<p>Er zijn nog geen lesgroepen. Nieuwe groepen starten automatisch met Software Developer.</p>@endforelse
        </section>
    </div>
</x-app-layout>
