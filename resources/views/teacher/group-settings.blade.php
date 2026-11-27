<x-app-layout headerTitle="Werkprocessen · {{ $group->name }}">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.groups.show', $group) }}" class="teacher-link back-button">← Terug naar groep {{ $group->name }}</a>
        @if ($errors->any())<div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 5000)" x-show="visible" x-transition.opacity role="alert" aria-live="assertive" class="panel text-red-700">{{ $errors->first() }}</div>@endif

        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">GROEPSINSTELLINGEN · {{ $group->name }}</p><h2>Gedeelde werkprocessen</h2></div></div>
            <p class="mb-5">Alle groepen binnen dezelfde opleiding gebruiken dezelfde werkprocessen. De beheerder beheert deze lijst bij de opleidingsinstellingen.</p>
            <div class="space-y-3">
                @forelse($group->workProcesses as $process)
                    <div class="border-t py-3"><p class="eyebrow">{{ $process->code }}</p><p>{{ $process->name }}</p></div>
                @empty
                    <p>Er zijn nog geen werkprocessen ingesteld.</p>
                @endforelse
            </div>
        </section>

    </div>
</x-app-layout>
