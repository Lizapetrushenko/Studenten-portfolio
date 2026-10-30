<x-app-layout headerTitle="Instellingen">
    <style>
        .education-settings table { width:100%; border-collapse:collapse; text-align:left; }
        .education-settings th, .education-settings td { padding:18px 16px; border-bottom:1px solid #e5e7eb; vertical-align:middle; }
        .education-settings tbody tr:last-child td { border-bottom:0; }
        .education-settings td input { width:100%; min-width:200px; }
    </style>
    <div class="portal-shell space-y-6 education-settings">
        @php($tab = in_array(request('tab'), ['opleidingen', 'kerntaken', 'werkprocessen'], true) ? request('tab') : 'opleidingen')
        <nav class="teacher-page-nav flex flex-wrap gap-3" aria-label="Instellingen onderdelen">
            <a class="teacher-link back-button" href="{{ route('admin.dashboard') }}">← Instellingen</a>
            @foreach(['opleidingen' => 'Opleidingen', 'kerntaken' => 'Kerntaken', 'werkprocessen' => 'Werkprocessen'] as $key => $label)
                <a class="button" href="{{ route('admin.settings', ['tab' => $key, 'program' => $program->id]) }}" @if($tab === $key) aria-current="page" style="background:#b94828" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        @if(session('status'))<div class="panel" role="status">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="panel text-red-700" role="alert">{{ $errors->first() }}</div>@endif
        @if($tab === 'opleidingen')
        <section class="panel" style="border:1px solid var(--accent)">
            <div class="section-title"><div><p class="eyebrow">OPLEIDINGEN</p><h2>Opleidingenlijst</h2></div><span class="count">{{ $programs->count() }} opleidingen</span></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead><tr><th scope="col">ID</th><th scope="col">KD-nummer</th><th scope="col">Naam opleiding</th><th scope="col">Acties</th></tr></thead>
                    <tbody>
                        @forelse($programs as $option)
                            <tr>
                                <td>{{ $option->id }}</td><td>{{ $option->kd_number ?? 'Niet ingesteld' }}</td><td>{{ $option->name }}</td>
                                <td><a class="button tiny-button" href="{{ route('admin.settings', ['program' => $option->id, 'tab' => 'werkprocessen']) }}">Werkprocessen</a>
                                    @if($option->name !== 'Software Developer')
                                        <details><summary class="cursor-pointer">Bewerken</summary><form method="POST" action="{{ route('admin.programs.update', $option) }}">@csrf @method('PATCH')<label>Naam opleiding<input name="name" value="{{ $option->name }}" maxlength="120" required></label><button class="button tiny-button">Opslaan</button></form></details>
                                    @endif
                                </td>
                            </tr>
                        @empty<tr><td colspan="4">Er zijn nog geen opleidingen.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            <details class="mt-6"><summary class="cursor-pointer font-semibold">KD en opleiding instellen</summary>
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
            </details>
        </section>
        @endif
        @if($tab === 'kerntaken')
        <section class="panel" style="border:1px solid var(--accent)">
            <div class="section-title"><div><p class="eyebrow">Kerntaken</p><h2>Kerntakenlijst</h2></div></div>
            <form method="GET" action="{{ route('admin.settings') }}" class="form-grid mb-5">
                <input type="hidden" name="tab" value="kerntaken">
                <label>Opleiding<select name="program">@foreach($programs as $option)<option value="{{ $option->id }}" @selected($option->id === $program->id)>{{ $option->name }}</option>@endforeach</select></label><div class="flex items-end"><button class="button">Openen</button></div>
            </form>
            <div class="overflow-x-auto"><table class="w-full text-left">
                <thead><tr><th scope="col">ID</th><th scope="col">Kerntaak</th><th scope="col">KD_ID</th></tr></thead>
                <tbody>@forelse($program->workProcesses->groupBy(fn ($process) => $process->number <= 5 ? 1 : 2) as $number => $items)
                    <tr><td>{{ $number }}</td><td>KT {{ $number }}</td><td>{{ $program->id }}</td></tr>
                @empty<tr><td colspan="3">Er zijn nog geen kerntaken met werkprocessen.</td></tr>@endforelse</tbody>
            </table></div>
        </section>
        @endif
        @if($tab === 'werkprocessen')
        <section class="panel" style="border:1px solid var(--accent)">
            <div class="section-title"><div><p class="eyebrow">02 / WERKPROCESSEN</p><h2>{{ $program->name }}</h2></div><span class="count">{{ $program->workProcesses->count() }} werkprocessen</span></div>
            <p class="mb-5">Wijzigingen gelden voor alle groepen die aan deze opleiding zijn gekoppeld.</p>
            <form method="GET" action="{{ route('admin.settings') }}" class="form-grid mb-5"><input type="hidden" name="tab" value="werkprocessen"><label>Opleiding<select name="program">@foreach($programs as $option)<option value="{{ $option->id }}" @selected($option->id === $program->id)>{{ $option->name }}</option>@endforeach</select></label><div class="flex items-end"><button class="button">Openen</button></div></form>
            <div class="overflow-x-auto"><table class="w-full text-left"><thead><tr><th scope="col">Code</th><th scope="col">Naam werkproces</th><th scope="col">Acties</th></tr></thead><tbody>
            @forelse($program->workProcesses as $process)
                <tr>
                    <td>{{ $process->number <= 5 ? 'K1W'.$process->number : 'K2W'.($process->number - 5) }}</td>
                    <td><input form="process-{{ $process->id }}" aria-label="Naam werkproces {{ $process->number }}" name="name" value="{{ $process->name }}" maxlength="120" required></td>
                    <td><div class="flex flex-wrap gap-3">
                    <form id="process-{{ $process->id }}" method="POST" action="{{ route('admin.processes.update', [$program, $process]) }}">@csrf @method('PATCH')<button class="button" type="submit">Opslaan</button></form>
                    <form method="POST" action="{{ route('admin.processes.destroy', [$program, $process]) }}" data-confirm="Dit werkproces verwijderen uit de opleiding en de gekoppelde groepen?">@csrf @method('DELETE')<button class="button secondary-button" type="submit">Verwijderen</button></form>
                    </div></td>
                </tr>
            @empty<tr><td colspan="3">Voeg het eerste werkproces voor deze opleiding toe.</td></tr>@endforelse
            </tbody></table></div>
            <form method="POST" action="{{ route('admin.processes.store', $program) }}" class="form-grid border-t pt-5">@csrf<label>Nieuw werkproces<input name="name" maxlength="120" required placeholder="Naam van het werkproces"></label><div class="flex items-end"><button class="button">Werkproces toevoegen</button></div></form>
        </section>
        @endif
        @if($tab === 'opleidingen')
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">03 / GROEPEN</p><h2>Opleiding per groep</h2></div></div>
            @forelse($groups as $group)
                <form method="POST" action="{{ route('admin.groups.program', $group) }}" class="form-grid border-t py-4">@csrf @method('PATCH')
                    <label>{{ $group->name }} · {{ $group->students_count }} studenten<select name="education_program_id">@foreach($programs as $option)<option value="{{ $option->id }}" @selected($option->id === $group->education_program_id)>{{ $option->name }}</option>@endforeach</select></label><div class="flex items-end"><button class="button">Opleiding opslaan</button></div>
                </form>
            @empty<p>Er zijn nog geen lesgroepen. Nieuwe groepen starten automatisch met Software Developer.</p>@endforelse
        </section>
        @endif
    </div>
</x-app-layout>
