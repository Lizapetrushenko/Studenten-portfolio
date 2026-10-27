<x-app-layout headerTitle="Docentdashboard">
    <div class="portal-shell space-y-6">
        <section class="panel">
            <p class="eyebrow">DOCENT</p>
            <h2 class="text-2xl font-semibold">Welkom op je dashboard</h2>
            <p class="mt-2">Kies wat je wilt beheren of bekijken.</p>
        </section>
        <section class="screen-grid">
            <a class="screen-card" href="{{ route('teacher.groups.index') }}">
                <span class="tag">{{ $groupCount }} groepen</span><h2>Groepen</h2>
                <p>Bekijk je groepen, maak groepen aan en open de groepspagina.</p><strong>Groepen openen →</strong>
            </a>
            <a class="screen-card" href="{{ route('teacher.students.index') }}">
                <span class="tag">{{ $studentCount }} studenten</span><h2>Studenten</h2>
                <p>Bekijk de studenten en open hun profiel of portfolio.</p><strong>Studenten bekijken →</strong>
            </a>
        </section>
    </div>
</x-app-layout>
