<x-app-layout headerTitle="Werkprocessen beheren">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.dashboard') }}" class="teacher-link back-button">← Terug naar dashboard</a>
        @if ($errors->any())<div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 5000)" x-show="visible" x-transition.opacity role="alert" aria-live="assertive" class="panel text-red-700">{{ $errors->first() }}</div>@endif
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">DOCENT</p><h2>Gedeelde werkprocessen per opleiding</h2></div></div>
            <p class="mb-5">Groepen met dezelfde opleiding tonen dezelfde werkprocessen. Beheer de curriculumlijst via de opleidingsinstellingen.</p>
            @forelse($groups as $group)
                <div class="border-t py-5">
                    <div class="section-title"><h3 class="text-xl font-semibold">{{ $group->name }}</h3><a class="button" href="{{ route('teacher.groups.settings', $group) }}">Instellingen openen</a></div>
                    <div class="space-y-3">
                        @forelse($group->workProcesses as $process)
                            <div class="border-b pb-3"><span class="eyebrow">{{ $process->code }}</span><p>{{ $process->name }}</p></div>
                        @empty<p>Er zijn nog geen werkprocessen voor deze groep.</p>@endforelse
                    </div>
                </div>
            @empty<p>Je hebt nog geen groepen. Maak eerst een groep aan.</p>@endforelse
        </section>
    </div>
</x-app-layout>
