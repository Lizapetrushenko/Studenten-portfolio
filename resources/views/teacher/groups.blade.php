<x-app-layout headerTitle="Groepen">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.dashboard') }}" class="teacher-link back-button">← Terug naar dashboard</a>
        @if (session('status'))<div class="panel" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="panel text-red-700">{{ $errors->first() }}</div>@endif
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">DOCENT</p><h2>Groep aanmaken</h2></div></div>
            <form method="POST" action="{{ route('teacher.groups.store') }}" class="teacher-add-student">@csrf
                <label>Naam van de groep<input name="name" placeholder="Bijvoorbeeld BOL 2A" required></label>
                <button type="submit" class="button">Groep aanmaken</button>
            </form>
        </section>
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">BASISGROEPEN</p><h2>Studentstatus</h2></div><span class="count">{{ $baseGroups->sum('students_count') }} studenten</span></div>
            <p class="mb-5">Deze vaste groepen laten zien in welke status studenten staan.</p>
            <div class="screen-grid">
                @foreach($baseGroups as $group)
                    <a class="screen-card" href="{{ route('teacher.groups.show', $group) }}"><span class="tag">{{ $group->students_count }} studenten</span><h2>{{ $group->name }}</h2><p>Studenten met status {{ strtolower($group->name) }} bekijken.</p><strong>Studenten bekijken →</strong></a>
                @endforeach
            </div>
        </section>
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">EIGEN GROEPEN</p><h2>Mijn groepen</h2></div><span class="count">{{ $groups->count() }} groepen</span></div>
            <div class="screen-grid">
                @forelse($groups as $group)
                    <a class="screen-card" href="{{ route('teacher.groups.show', $group) }}"><span class="tag">{{ $group->students_count }} studenten</span><h2>{{ $group->name }}</h2><p>Bekijk studenten en de voortgang van hun portfolio's.</p><strong>Groep openen →</strong></a>
                @empty<p>Je hebt nog geen groepen aangemaakt.</p>@endforelse
            </div>
        </section>
    </div>
</x-app-layout>
