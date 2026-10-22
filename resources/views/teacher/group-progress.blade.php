<x-app-layout headerTitle="Voortgang · {{ $group->name }}">
    <div class="portal-shell space-y-6">
        <a href="{{ route('teacher.groups.show', $group) }}" class="teacher-link back-button">← Terug naar groep {{ $group->name }}</a>
        @if (session('status'))<div class="panel" role="status">{{ session('status') }}</div>@endif
        <section class="panel progress-panel">
            <div class="section-title">
                <div><p class="eyebrow">GROEP · {{ $group->name }}</p><h2>Voortgangsoverzicht</h2></div>
                <span class="count">{{ $rows->count() }} van {{ $group->students->count() }} studenten</span>
            </div>
            <p>Bekijk de bewijsstukken per student en werkproces. Klik op een nummer om het bewijsstuk te openen. Met Nakijken markeer je een bewijsstuk dat je gaat beoordelen.</p>
            <form method="GET" action="{{ route('teacher.groups.progress', $group) }}" class="progress-filters">
                <label>Zoeken op naam<input type="search" name="zoek" value="{{ $search }}" maxlength="120" placeholder="Naam van student"></label>
                <label>Status<span class="student-select-wrap">
                    <select name="status"><option value="">Alle statussen</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select>
                    <svg class="student-select-arrow" aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg>
                </span></label>
                <button type="submit" class="button">Filteren</button>
                @if($search !== '' || $status !== '')<a class="teacher-link" href="{{ route('teacher.groups.progress', $group) }}">Filters wissen</a>@endif
            </form>
            <div class="progress-legend" aria-label="Legenda bewijsstatus">
                @foreach($statuses as $value => $label)<span><i aria-hidden="true" class="progress-key progress-{{ str_replace(' ', '-', $value) }}"></i>{{ $label }}</span>@endforeach
            </div>
            @if($processes->isEmpty())
                <p class="progress-empty">Er zijn nog geen werkprocessen aan deze groep gekoppeld.</p>
            @else
                <div class="progress-scroll" tabindex="0" role="region" aria-label="Voortgangstabel, horizontaal scrollbaar">
                    <table class="progress-table">
                        <caption class="sr-only">Bewijsstukken per student en werkproces voor {{ $group->name }}</caption>
                        <thead><tr><th scope="col">Student</th>@foreach($processes as $process)<th scope="col"><abbr title="{{ $process->name }}">{{ $process->number <= 5 ? 'K1W'.$process->number : 'K2W'.($process->number - 5) }}</abbr></th>@endforeach</tr></thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr><th scope="row"><a class="teacher-link" href="{{ route('teacher.students.show', $row['student']) }}">{{ $row['student']->displayName() }}</a>@if(!$row['portfolio'])<small>Nog geen portfolio</small>@endif</th>
                                @foreach($processes as $process)
                                    <td><div class="progress-items">
                                        @forelse($row['evidence']->get((string) $process->number, collect()) as $item)
                                            @php($itemStatus = $item->status ?: 'niet bekeken')
                                            @php($itemLabel = ($item->title ?: 'Bewijsstuk '.$item->item_number).' · '.($statuses[$itemStatus] ?? $itemStatus))
                                            <div class="progress-item"><a href="{{ route('teacher.students.portfolio', $row['student']) }}#bewijs-{{ $item->id }}" class="progress-evidence progress-{{ str_replace(' ', '-', $itemStatus) }}" title="{{ $itemLabel }}" aria-label="{{ $row['student']->displayName() }} · {{ $itemLabel }}">{{ $item->item_number ?: $loop->iteration }}</a>
                                            @if($row['canReview'] && in_array($item->status, ['ingeleverd', 'niet akkoord'], true))
                                                <form method="POST" action="{{ route('teacher.evidence.review', $item) }}">@csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="nakijken">
                                                    <button type="submit" class="progress-review-button" aria-label="{{ $item->title ?: 'Bewijsstuk' }} van {{ $row['student']->displayName() }} nakijken">Nakijken</button>
                                                </form>
                                            @elseif($item->status === 'nakijken')<small class="progress-review-label">Nakijken</small>@endif</div>
                                        @empty<span class="progress-dash" aria-label="Geen bewijsstukken">—</span>@endforelse
                                    </div></td>
                                @endforeach</tr>
                            @empty<tr><td colspan="{{ $processes->count() + 1 }}" class="progress-empty">{{ $group->students->isEmpty() ? 'Er zijn nog geen studenten aan deze groep gekoppeld.' : 'Geen studenten gevonden met deze filters.' }}</td></tr>@endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
