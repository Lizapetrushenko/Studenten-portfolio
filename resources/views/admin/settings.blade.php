<x-app-layout headerTitle="Instellingen">
    <style>
        .education-settings table { width:100%; border-collapse:collapse; text-align:left; }
        .education-settings th, .education-settings td { padding:10px 12px; border-bottom:1px solid #e5e7eb; vertical-align:middle; }
        .education-settings tbody tr:last-child td { border-bottom:0; }
        .education-settings td input { width:100%; min-width:130px; }
        .education-settings .program-editor { min-width:720px; }
        .education-settings .program-editor input { min-width:0; }
        .education-settings .program-delete { width:42px; min-width:42px; height:42px; padding:8px; border:0; border-radius:9px; color:#a33d27; background:#fff0ea; cursor:pointer; }
        .education-settings .program-delete:hover { color:#fff; background:#b94828; }
        .education-settings .program-delete svg { width:20px; height:20px; margin:auto; }
        .education-settings .new-work-process-form { display:grid; grid-template-columns:minmax(0, 1.25fr) minmax(260px, 1fr); gap:14px; align-items:end; border-top:1px solid #e5e7eb; padding-top:20px; }
        .education-settings .new-work-process-form label { display:flex; flex-direction:column; gap:7px; }
        .education-settings .new-process-name, .education-settings .new-process-submit { grid-column:1/-1; }
        @media(max-width:700px) { .education-settings .new-work-process-form { grid-template-columns:1fr; } .education-settings .new-process-name, .education-settings .new-process-submit { grid-column:auto; } }
    </style>
    <div class="portal-shell space-y-6 education-settings">
        <nav class="teacher-page-nav flex flex-wrap gap-3" aria-label="Instellingen onderdelen">
            <a class="teacher-link back-button" href="{{ route('admin.dashboard') }}">← Instellingen</a>
            <span class="count">Curriculumbeheer · {{ $program->name }}</span>
        </nav>
        @if($errors->any())<div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 5000)" x-show="visible" x-transition.opacity class="panel text-red-700" role="alert" aria-live="assertive">{{ $errors->first() }}</div>@endif
        <section class="panel" style="border:1px solid var(--accent)">
            <div class="section-title"><div><p class="eyebrow">OPLEIDINGEN</p><h2>Opleidingenlijst</h2></div><span class="count">{{ $programs->count() }} opleidingen</span></div>
            <form method="POST" action="{{ route('admin.programs.save') }}" id="programs-form">
                @csrf @method('PUT')
                <input type="hidden" name="current_program" value="{{ $program->id }}">
            </form>
            <div class="overflow-x-auto">
                <table class="program-editor text-left">
                    <thead><tr><th scope="col">ID</th><th scope="col">Opleidingscode</th><th scope="col">Naam opleiding</th><th scope="col"><span class="sr-only">Verwijderen</span></th></tr></thead>
                    <tbody id="program-rows">
                        @foreach($programs as $index => $option)
                            <tr>
                                <td>{{ $option->id }}<input type="hidden" form="programs-form" name="programs[{{ $index }}][id]" value="{{ $option->id }}"></td>
                                <td><input form="programs-form" name="programs[{{ $index }}][code]" value="{{ old("programs.$index.code", $option->code) }}" maxlength="100" required aria-label="Opleidingscode"></td>
                                <td><input form="programs-form" name="programs[{{ $index }}][name]" value="{{ old("programs.$index.name", $option->name) }}" maxlength="120" required aria-label="Naam opleiding"></td>
                                <td><button class="program-delete" type="submit" form="delete-program-{{ $option->id }}" aria-label="Opleiding {{ $option->name }} verwijderen" title="Opleiding verwijderen"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2m3 0-.8 14H5.8L5 6m4 4v6m6-6v6"/></svg></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @foreach($programs as $option)
                <form id="delete-program-{{ $option->id }}" method="POST" action="{{ route('admin.programs.destroy', $option) }}" class="hidden" data-confirm="Opleiding {{ $option->name }} verwijderen? Dit kan alleen als er geen groepen aan gekoppeld zijn.">@csrf @method('DELETE')</form>
            @endforeach
            <div class="mt-5 flex flex-wrap gap-3">
                <button class="button" type="button" id="add-program-row">+ Opleiding toevoegen</button>
                <button class="button" type="submit" form="programs-form">Alles opslaan</button>
            </div>
            <form method="GET" action="{{ route('admin.settings') }}" class="form-grid mt-5 border-t pt-5">
                <label>Kerntaken en werkprocessen beheren voor<select name="program">@foreach($programs as $option)<option value="{{ $option->id }}" @selected($option->id === $program->id)>{{ $option->code ? $option->code.' · ' : '' }}{{ $option->name }}</option>@endforeach</select></label>
                <div class="flex items-end"><button class="button" type="submit">Opleiding openen</button></div>
            </form>
            <script>
                (() => {
                    const rows = document.getElementById('program-rows');
                    const addButton = document.getElementById('add-program-row');
                    const educationNames = {{ Illuminate\Support\Js::from((object) $educationNames) }};
                    rows.addEventListener('input', event => {
                        const codeInput = event.target;
                        if (!codeInput.matches('input[name$="[code]"]')) return;
                        const row = codeInput.closest('tr');
                        const nameInput = row.querySelector('input[name$="[name]"]');
                        let message = row.querySelector('[data-lookup-message]');
                        if (!message) {
                            message = document.createElement('small');
                            message.dataset.lookupMessage = '';
                            message.setAttribute('role', 'status');
                            codeInput.parentElement.appendChild(message);
                        }
                        const code = codeInput.value.trim();
                        const name = Object.hasOwn(educationNames, code) ? educationNames[code] : null;
                        if (name) {
                            if (nameInput.value.toLowerCase() !== name.toLowerCase()) nameInput.value = name;
                            message.textContent = '';
                        } else {
                            if (nameInput.dataset.lookupName && nameInput.value === nameInput.dataset.lookupName) nameInput.value = '';
                            message.textContent = code ? 'Code niet gevonden. Vul de opleidingsnaam handmatig in.' : '';
                        }
                        nameInput.dataset.lookupName = name || '';
                    });
                    let nextIndex = rows.querySelectorAll('tr').length;
                    addButton.addEventListener('click', () => {
                        const row = document.createElement('tr');
                        row.innerHTML = `<td>Nieuw</td><td><input form="programs-form" name="programs[${nextIndex}][code]" maxlength="100" required aria-label="Opleidingscode" placeholder="Bijvoorbeeld 25998"></td><td><input form="programs-form" name="programs[${nextIndex}][name]" maxlength="120" required aria-label="Naam opleiding" placeholder="Naam opleiding"></td><td><button class="program-delete" type="button" aria-label="Nieuwe opleidingsregel verwijderen" title="Regel verwijderen"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2m3 0-.8 14H5.8L5 6m4 4v6m6-6v6"/></svg></button></td>`;
                        rows.appendChild(row);
                        nextIndex++;
                        row.querySelector('input').focus();
                    });
                    rows.addEventListener('click', event => {
                        const removeButton = event.target.closest('button[type="button"].program-delete');
                        if (removeButton) removeButton.closest('tr').remove();
                    });
                })();
            </script>
        </section>
        <section class="panel" style="border:1px solid var(--accent)">
            <div class="section-title"><div><p class="eyebrow">01 / KERNTAKEN</p><h2>Kerntakenlijst</h2></div><span class="count">{{ $program->coreTasks->count() }} kerntaken</span></div>
            <form method="GET" action="{{ route('admin.settings') }}" class="form-grid mb-5">
                <label>Opleiding<select name="program">@foreach($programs as $option)<option value="{{ $option->id }}" @selected($option->id === $program->id)>{{ $option->name }}</option>@endforeach</select></label><div class="flex items-end"><button class="button">Openen</button></div>
            </form>
            <div class="overflow-x-auto"><table class="w-full text-left">
                <thead><tr><th scope="col">ID</th><th scope="col">Kerntaak</th><th scope="col">KD_ID</th><th scope="col">KT 1</th><th scope="col">KT 2</th><th scope="col">Acties</th></tr></thead>
                <tbody>@forelse($program->coreTasks as $task)
                    <tr><td><input form="task-{{ $task->id }}" name="task_number" value="{{ $task->task_number }}" maxlength="50" required aria-label="Code kerntaak"></td><td><input form="task-{{ $task->id }}" name="name" value="{{ $task->name }}" maxlength="255" required aria-label="Naam kerntaak"></td><td><div class="flex flex-wrap gap-2"><form id="task-{{ $task->id }}" method="POST" action="{{ route('admin.core-tasks.update', [$program, $task]) }}">@csrf @method('PATCH')<button class="button tiny-button">Opslaan</button></form><form method="POST" action="{{ route('admin.core-tasks.destroy', [$program, $task]) }}" data-confirm="Deze lege kerntaak verwijderen?">@csrf @method('DELETE')<button class="button secondary-button tiny-button">Verwijderen</button></form></div></td></tr>
                @empty<tr><td colspan="3">Er zijn nog geen kerntaken voor deze opleiding.</td></tr>@endforelse</tbody>
            </table></div>
            <form method="POST" action="{{ route('admin.core-tasks.store', $program) }}" class="form-grid mt-5 border-t pt-5">@csrf<label>Code kerntaak<input name="task_number" maxlength="50" value="{{ old('task_number') }}" placeholder="Bijvoorbeeld B1-K3" required></label><label>Naam kerntaak<input name="name" maxlength="255" value="{{ old('name') }}" placeholder="Naam van de kerntaak" required></label><div class="flex items-end"><button class="button">Kerntaak toevoegen</button></div></form>
        </section>
        <section class="panel" style="border:1px solid var(--accent)">
            <div class="section-title"><div><p class="eyebrow">02 / WERKPROCESSEN</p><h2>{{ $program->name }}</h2></div><span class="count">{{ $program->workProcesses->count() }} werkprocessen</span></div>
            <p class="mb-5">Wijzigingen gelden voor alle groepen die aan deze opleiding zijn gekoppeld.</p>
            <form method="GET" action="{{ route('admin.settings') }}" class="form-grid mb-5"><label>Opleiding<select name="program">@foreach($programs as $option)<option value="{{ $option->id }}" @selected($option->id === $program->id)>{{ $option->code ? $option->code.' · ' : '' }}{{ $option->name }}</option>@endforeach</select></label><div class="flex items-end"><button class="button">Opleiding openen</button></div></form>
            <div class="overflow-x-auto"><table class="w-full text-left"><thead><tr><th scope="col">Code</th><th scope="col">Naam werkproces</th><th scope="col">Acties</th></tr></thead><tbody>
            @forelse($program->workProcesses as $process)
                <tr>
                    <td>{{ $process->code ?? $process->number }}</td>
                    <td><input form="process-{{ $process->id }}" aria-label="Naam werkproces {{ $process->number }}" name="name" value="{{ $process->name }}" maxlength="120" required></td>
                    <td><div class="flex flex-wrap gap-3">
                    <form id="process-{{ $process->id }}" method="POST" action="{{ route('admin.processes.update', [$program, $process]) }}">@csrf @method('PATCH')<button class="button" type="submit">Opslaan</button></form>
                    <form method="POST" action="{{ route('admin.processes.destroy', [$program, $process]) }}" data-confirm="Dit werkproces verwijderen uit de opleiding en de gekoppelde groepen?">@csrf @method('DELETE')<button class="button secondary-button" type="submit">Verwijderen</button></form>
                    </div></td>
                </tr>
            @empty<tr><td colspan="3">Voeg het eerste werkproces voor deze opleiding toe.</td></tr>@endforelse
            </tbody></table></div>
            <form method="POST" action="{{ route('admin.processes.store', $program) }}" class="new-work-process-form">@csrf
                <label>Kerntaak<select name="core_task_id" required>
                    @foreach($program->coreTasks as $task)<option value="{{ $task->id }}" @selected((string) old('core_task_id') === (string) $task->id)>{{ $task->task_number }} · {{ $task->name }}</option>@endforeach
                </select></label>
                <label class="new-process-name">Naam van werkproces<input name="name" value="{{ old('name') }}" maxlength="120" required placeholder="Naam van het werkproces"></label>
                <div class="new-process-submit"><button class="button">Werkproces toevoegen</button></div>
            </form>
        </section>

    </div>
</x-app-layout>
