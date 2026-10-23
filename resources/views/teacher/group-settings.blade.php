<x-app-layout headerTitle="Werkprocessen · {{ $group->name }}">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.groups.show', $group) }}" class="teacher-link back-button">← Terug naar groep {{ $group->name }}</a>
        @if (session('status'))<div class="panel" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="panel text-red-700">{{ $errors->first() }}</div>@endif

        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">GROEPSINSTELLINGEN · {{ $group->name }}</p><h2>Werkprocessen beheren</h2></div></div>
            <p class="mb-5">Werkprocessen die je hier instelt, verschijnen automatisch in de portfolio’s van studenten in deze groep.</p>
            <div class="space-y-3">
                @forelse($group->workProcesses as $process)
                    <form method="POST" action="{{ route('teacher.groups.work-processes.update', [$group, $process]) }}" class="work-process-edit-row border-t py-3">
                        @csrf @method('PATCH')
                        <p class="eyebrow">{{ $process->number <= 5 ? 'K1W'. $process->number : 'K2W'. ($process->number - 5) }}</p>
                        <label>Naam van werkproces<input name="name" value="{{ $process->name }}" maxlength="120" required></label>
                        <button class="button work-process-save" type="submit">Opslaan</button>
                    </form>
                @empty
                    <p>Er zijn nog geen werkprocessen ingesteld.</p>
                @endforelse
            </div>
        </section>

        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">NIEUW</p><h2>Werkproces toevoegen</h2></div></div>
            <form method="POST" action="{{ route('teacher.groups.work-processes.store', $group) }}" class="form-grid">@csrf
                <label>Naam van werkproces<input name="name" required maxlength="120" placeholder="Bijvoorbeeld: Reflecteren"></label>
                <div class="full"><button class="button" type="submit">Werkproces toevoegen</button></div>
            </form>
        </section>
    </div>
</x-app-layout>
