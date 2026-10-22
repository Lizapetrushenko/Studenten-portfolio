<x-app-layout headerTitle="Portfolio van {{ $student->name }}">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.students.show', $student) }}" class="teacher-link back-button">← Terug naar studentgegevens</a>
        @if (session('status'))<div class="panel" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="panel text-red-700">{{ $errors->first() }}</div>@endif
        <section class="panel">
            <p class="eyebrow">STUDENTPORTFOLIO</p><h2>{{ $portfolio->title }}</h2><p>{{ $student->displayName() }} · {{ $student->email }} · {{ $student->student_number }} · {{ $student->study_type ?: $portfolio->study }}</p>
            @if($portfolio->bio)<p class="mt-3">{{ $portfolio->bio }}</p>@endif
        </section>
        @foreach($workProcesses as $process)
            @php($items = $portfolio->evidence->where('work_process', $process->number)->where('status', '!=', 'idee'))
            <section class="panel">
                <div class="section-title"><div><p class="eyebrow">WERKPROCES {{ $process->number }}</p><h2>{{ $process->name }}</h2></div><span class="count">{{ $items->count() }} bewijsstukken</span></div>
                @forelse($items as $item)
                    <article id="bewijs-{{ $item->id }}" class="border-t py-5 space-y-3">
                        <div class="flex flex-wrap justify-between gap-3"><h3 class="text-lg font-semibold">{{ $item->title ?: 'Bewijsstuk '.$item->item_number }}</h3><span class="tag">{{ $item->status ?: 'Nog niet beoordeeld' }}</span></div>
                        @if($item->description)<p>{{ $item->description }}</p>@endif
                        @if($item->idea)<p><strong>Idee:</strong> {{ $item->idea }}</p>@endif
                        @if($item->note)<p><strong>Studentnotitie:</strong> {{ $item->note }}</p>@endif
                        @if($item->feedback)<p><strong>Notitie docent:</strong> {{ $item->feedback }}</p>@endif
                        @if($item->submitted_where)<p><strong>Ingeleverd via:</strong> {{ $item->submitted_where }}</p>@endif
                        @if($item->url)<p><a class="teacher-link" href="{{ $item->url }}" target="_blank" rel="noopener">Bewijsstuk openen</a></p>@endif
                        @if($item->file_path)<p class="flex gap-4"><a class="teacher-link" href="{{ route('evidence.open', $item) }}" target="_blank" rel="noopener">Bestand openen</a><a class="teacher-link" href="{{ route('evidence.download', $item) }}">Bestand downloaden</a></p>@endif
                        @if($canReview && $item->status !== 'akkoord')<form method="POST" action="{{ route('teacher.evidence.review', $item) }}" class="form-grid">@csrf @method('PATCH')
                            <label>Beoordeling<select name="status" required><option value="">Kies een beoordeling</option><option value="nakijken" @selected($item->status === 'nakijken')>Nakijken</option><option value="akkoord" @selected($item->status === 'akkoord')>Akkoord</option><option value="niet akkoord" @selected($item->status === 'niet akkoord')>Niet akkoord</option></select></label>
                            <label>Notitie docent<textarea name="feedback" rows="2" maxlength="2000">{{ $item->feedback }}</textarea></label>
                            <div class="full"><button class="button">Beoordeling opslaan</button></div>
                        </form>@elseif($item->status === 'akkoord')<p class="font-semibold text-green-700">Dit bewijsstuk is akkoord en vergrendeld.</p>@elseif(!$canReview)<p class="text-gray-600">Je bent niet gekoppeld als beoordelaar en kunt dit bewijsstuk niet beoordelen.</p>@endif
                    </article>
                @empty<p class="py-4">Er zijn nog geen bewijsstukken toegevoegd aan dit werkproces.</p>@endforelse
            </section>
        @endforeach
    </div>
</x-app-layout>
