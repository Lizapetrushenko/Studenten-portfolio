<x-app-layout headerTitle="Groepen">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.dashboard') }}" class="underline">← Terug naar dashboard</a>
        @if ($errors->any())<div class="panel text-red-700">{{ $errors->first() }}</div>@endif
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">DOCENT</p><h2>Groep aanmaken</h2></div></div>
            <form method="POST" action="{{ route('teacher.groups.store') }}" class="form-grid">@csrf
                <label>Naam van de groep<input name="name" placeholder="Bijvoorbeeld BOL 2A" required></label>
                <div class="full"><button class="button">Groep aanmaken</button></div>
            </form>
        </section>
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">BASISGROEPEN</p><h2>Studentstatus</h2></div><span class="count">{{ $baseGroups->sum('students_count') }} studenten</span></div>
            <p class="mb-5">Deze vaste groepen laten zien in welke status studenten staan.</p>
            <div class="screen-grid">
                @foreach($baseGroups as $group)
                    <a class="screen-card text-center" href="{{ route('teacher.groups.show', $group) }}"><x-group-status-photo :status="$group->lifecycle_status" size="h-36 w-36 mb-3" /><span class="tag">{{ $group->students_count }} studenten</span><h2>{{ $group->name }}</h2><p>Studenten met status {{ strtolower($group->name) }} bekijken.</p><strong>Studenten bekijken →</strong></a>
                @endforeach
            </div>
        </section>
        <section class="panel">
            <div class="section-title"><div><p class="eyebrow">EIGEN GROEPEN</p><h2>Mijn groepen</h2></div><span class="count">{{ $groups->count() }} groepen</span></div>
            <div class="screen-grid">
                @forelse($groups as $group)
                    <a class="screen-card text-center" href="{{ route('teacher.groups.show', $group) }}">@if($group->lifecycle_status)<x-group-status-photo :status="$group->lifecycle_status" size="h-36 w-36 mb-3" />@elseif($group->photo_path)<img src="{{ asset('storage/'.$group->photo_path) }}" alt="Foto van {{ $group->name }}" class="mx-auto mb-3 h-36 w-36 rounded-full object-cover">@else<div class="group-photo-placeholder mx-auto mb-3" aria-hidden="true"></div>@endif<span class="tag">{{ $group->students_count }} studenten</span><h2>{{ $group->name }}</h2><p>Bekijk studenten en de voortgang van hun portfolio's.</p><strong>Groep openen →</strong></a>
                @empty<p>Je hebt nog geen groepen aangemaakt.</p>@endforelse
            </div>
        </section>
    </div>
</x-app-layout>
