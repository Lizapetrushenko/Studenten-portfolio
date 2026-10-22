<x-app-layout headerTitle="Werkprocessen beheren">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.dashboard') }}" class="teacher-link back-button">← Terug naar dashboard</a>
        @if (session('status'))<div class="panel" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="panel text-red-700">{{ $errors->first() }}</div>@endif
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">DOCENT</p><h2>Werkprocessen per groep</h2></div></div>
            <p class="mb-5">Wijzigingen zijn direct zichtbaar in de portfolio’s van studenten in de betreffende groep.</p>
            @forelse($groups as $group)
                <div class="border-t py-5">
                    <div class="section-title"><h3 class="text-xl font-semibold">{{ $group->name }}</h3><a class="button" href="{{ route('teacher.groups.settings', $group) }}">Instellingen openen</a></div>
                    <div class="space-y-3">
                        @forelse($group->workProcesses as $process)
                            <div class="grid gap-3 border-b pb-3 md:grid-cols-[auto_1fr_auto] md:items-end">
                                <span class="eyebrow">{{ $process->number <= 5 ? 'K1W'.$process->number : 'K2W'.($process->number - 5) }}</span>
                                <form method="POST" action="{{ route('teacher.groups.work-processes.update', [$group, $process]) }}" class="flex gap-2">@csrf @method('PATCH')
                                    <label class="sr-only" for="process-{{ $group->id }}-{{ $process->id }}">Naam werkproces</label>
                                    <input id="process-{{ $group->id }}-{{ $process->id }}" name="name" value="{{ $process->name }}" maxlength="120" required>
                                    <button class="button" type="submit">Opslaan</button>
                                </form>
                                <form method="POST" action="{{ route('teacher.groups.work-processes.destroy', [$group, $process]) }}" onsubmit="return confirm('Werkproces verwijderen?')">@csrf @method('DELETE')<button class="button button-secondary" type="submit">Verwijderen</button></form>
                            </div>
                        @empty<p>Er zijn nog geen werkprocessen voor deze groep.</p>@endforelse
                    </div>
                    <form method="POST" action="{{ route('teacher.groups.work-processes.store', $group) }}" class="mt-4 flex flex-wrap items-end gap-3">@csrf
                        <label class="flex-1">Nieuw werkproces<input name="name" maxlength="120" required placeholder="Bijvoorbeeld: Reflecteren"></label>
                        <button class="button" type="submit">Werkproces toevoegen</button>
                    </form>
                </div>
            @empty<p>Je hebt nog geen groepen. Maak eerst een groep aan.</p>@endforelse
        </section>
    </div>
</x-app-layout>
