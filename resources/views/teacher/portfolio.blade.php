<x-app-layout headerTitle="Portfolio van {{ $student->name }}">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.students.show', $student) }}" class="underline">← Terug naar studentgegevens</a>
        @if ($errors->any())<div class="panel text-red-700">{{ $errors->first() }}</div>@endif
        <section class="panel">
            <p class="eyebrow">STUDENTPORTFOLIO</p><h2>{{ $portfolio->title }}</h2><p>{{ $student->displayName() }} · {{ $student->email }} · {{ $student->student_number }} · {{ $student->study_type ?: $portfolio->study }}</p>
            @if($portfolio->bio)<p class="mt-3">{{ $portfolio->bio }}</p>@endif
        </section>
        @foreach($workProcesses as $process)
            @php($items = $portfolio->evidence->where('work_process', $process->number)->whereIn('status', ['ingeleverd', 'akkoord', 'niet akkoord']))
            <section class="panel">
                <div class="section-title"><div><p class="eyebrow">WERKPROCES {{ $process->number }}</p><h2>{{ $process->name }}</h2></div><span class="count">{{ $items->count() }} bewijsstukken</span></div>
                @forelse($items as $item)
                    <article class="border-t py-5 space-y-3">
                        <div class="flex flex-wrap justify-between gap-3"><h3 class="text-lg font-semibold">{{ $item->title ?: 'Bewijsstuk '.$item->item_number }}</h3><span class="tag">{{ $item->status ?: 'Nog niet beoordeeld' }}</span></div>
                        @if($item->description)<p>{{ $item->description }}</p>@endif
                        @if($item->idea)<p><strong>Idee:</strong> {{ $item->idea }}</p>@endif
                        @if($item->note)<p><strong>Studentnotitie:</strong> {{ $item->note }}</p>@endif
                        @if($item->feedback)<p><strong>Notitie docent:</strong> {{ $item->feedback }}</p>@endif
                        @if($item->submitted_where)<p><strong>Ingeleverd via:</strong> {{ $item->submitted_where }}</p>@endif
                        @if($item->url)<p><a class="underline" href="{{ $item->url }}" target="_blank" rel="noopener">Bewijsstuk openen</a></p>@endif
                        @if($item->file_path)
                            @php($savedFileDisk = \Illuminate\Support\Facades\Storage::disk('local')->exists($item->file_path) ? 'local' : 'public')
                            @php($savedFileStorage = \Illuminate\Support\Facades\Storage::disk($savedFileDisk))
                            @php($savedFileSize = $savedFileStorage->exists($item->file_path) ? $savedFileStorage->size($item->file_path) : null)
                            <div class="saved-file-list">
                                <div class="saved-file-header"><span>Bestandsnaam</span><span>Grootte</span><span></span></div>
                                <div class="saved-file-row">
                                    <span class="saved-file-name" title="{{ $item->original_file_name ?: basename($item->file_path) }}"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 2.75h8l5 5v13.5H6z"/><path d="M14 2.75v5h5M8.5 15.5l1.6-4 1.6 4m-2.8-1.2h2.4m2.2 1.2v-4h1.2a1.4 1.4 0 0 1 0 2.8h-1.2"/></svg><span>{{ $item->original_file_name ?: basename($item->file_path) }}</span></span>
                                    <span class="saved-file-size">{{ $savedFileSize !== null ? ($savedFileSize >= 1048576 ? number_format($savedFileSize / 1048576, 0) . ' MB' : number_format($savedFileSize / 1024, 0) . ' KB') : 'Onbekend' }}</span>
                                    <a class="saved-file-download" href="{{ route('evidence.download', $item) }}"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v4h14v-4"/></svg>Downloaden</a>
                                </div>
                            </div>
                        @endif
                        @if($canReview && !in_array($item->status, ['akkoord', 'idee'], true))<form method="POST" action="{{ route('teacher.evidence.review', $item) }}" class="form-grid">@csrf @method('PATCH')
                            <label>Beoordeling<select name="status" required><option value="">Kies een beoordeling</option><option value="akkoord" @selected($item->status === 'akkoord')>Akkoord</option><option value="niet akkoord" @selected($item->status === 'niet akkoord')>Niet akkoord</option></select></label>
                            <label>Notitie docent<textarea name="feedback" rows="2" maxlength="2000">{{ $item->feedback }}</textarea></label>
                            <div class="full"><button class="button">Beoordeling opslaan</button></div>
                        </form>@elseif($item->status === 'akkoord')<p class="font-semibold text-green-700">Dit bewijsstuk is akkoord en vergrendeld.</p>@elseif(!$canReview)<p class="text-gray-600">Je bent niet gekoppeld als beoordelaar en kunt dit bewijsstuk niet beoordelen.</p>@endif
                    </article>
                @empty<p class="py-4">Er zijn nog geen bewijsstukken toegevoegd aan dit werkproces.</p>@endforelse
            </section>
        @endforeach
    </div>
</x-app-layout>
