<x-app-layout headerTitle="Mijn portfolio">
    <div class="portal-shell portfolio-page">
            <div class="portfolio-sections">
                <section class="panel portfolio-panel {{ !$portfolio ? 'portfolio-panel-empty' : '' }}" x-data="{ editing: false }">
                    <div class="section-title"><div><p class="eyebrow">01 / PROFIEL</p><h2>Portfolio-informatie</h2></div><div class="flex items-center gap-3"><span class="tag">{{ $portfolio ? 'Actief' : 'Start hier' }}</span>@if($portfolio && !$portfolioLocked && !$submitOnly)<button type="button" class="button tiny-button" @click.prevent="editing = !editing" x-text="editing ? 'Annuleren' : 'Aanpassen'"></button>@endif</div></div>
                    
                    @if($portfolio)
                        <!-- Portfolio Bestaande Info -->
                        <div class="portfolio-info" x-show="!editing">
                            <div class="info-item">
                                <label class="info-label">Projectnaam:</label>
                                <p class="info-value">{{ $portfolio->title }}</p>
                            </div>
                            <div class="info-item">
                                <label class="info-label">Leerweg:</label>
                                <p class="info-value">{{ auth()->user()->leerweg ?: 'Nog niet ingesteld' }}</p>
                            </div>
                            <div class="info-item">
                                <label class="info-label">Korte introductie:</label>
                                <p class="info-value">{{ $portfolio->bio }}</p>
                            </div>
                           
                        </div>
                        <form method="POST" action="{{ route('portfolio.store') }}" class="form-grid portfolio-form" x-show="editing" x-cloak>@csrf
                            <label>Projectnaam<input name="title" value="{{ $portfolio->title }}" placeholder="Schrijf zelf je projectnaam" required></label>
                            <label class="full">Korte introductie<textarea name="bio" rows="3" placeholder="Waar ben je trots op?">{{ $portfolio->bio }}</textarea></label>
                            <div class="full"><button class="button" type="submit">Wijzigingen opslaan</button></div>
                        </form>
                        @if(!$portfolioLocked && !$submitOnly && !$portfolio->evidence->contains('status', 'akkoord'))
                            <form method="POST" action="{{ route('portfolio.destroy', $portfolio) }}" class="mt-4" onsubmit="return confirm('Weet je zeker dat je je portfolio en alle bewijsstukken wilt verwijderen?')">@csrf @method('DELETE')<button class="evidence-delete-button" type="submit"><svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M9 6V4h6v2M5 6l1 14h12l1-14M10 10v6M14 10v6" /></svg>Portfolio verwijderen</button></form>
                        @endif
                    @else
                        <!-- Portfolio Invulformulier -->
                        @if(!$portfolioLocked)
                        <form method="POST" action="{{ route('portfolio.store') }}" class="form-grid portfolio-form">@csrf
                            <label>Projectnaam<input name="title" value="{{ old('title') }}" placeholder="Schrijf zelf je projectnaam" required></label>
                            <label>Leerweg<input value="{{ auth()->user()->leerweg ?: 'Nog niet ingesteld' }}" readonly></label>
                            <label class="full">Korte introductie<textarea name="bio" rows="3" placeholder="Waar ben je trots op?">{{ old('bio') }}</textarea></label>
                            <div class="full"><button class="button" type="submit">Portfolio opslaan</button></div>
                        </form>
                        @else
                            <p>Je portfolio is vergrendeld voor deze groepsstatus.</p>
                        @endif
                    @endif
                </section>

                @if($portfolio)
                    <!-- Spin Diagram Widget Section -->
                    <section class="panel spin-chart-panel">
                        <div class="section-title"><div><p class="eyebrow">03 / OVERZICHT</p><h2>Van je werkzaamheden</h2></div></div>
                        @if(count($processes))
                            <div class="spin-chart-widget-small">
                                <div class="spin-chart-container-small" data-chart-data="{{ $spinChartData }}" data-chart-labels="{{ json_encode(collect($processes)->map(fn ($process) => $process['kerntaak'] . $process['work_label'])->values()) }}">
                                    <canvas id="spinChart"></canvas>
                                </div>
                                <div class="spin-legend-small" aria-label="Legenda status bewijsstukken">
                                    <span class="legend-item"><i class="legend-dot" style="background:#22c55e"></i>akkoord</span>
                                    <span class="legend-item"><i class="legend-dot" style="background:#dc2626"></i>niet akkoord</span>
                                    <span class="legend-item"><i class="legend-dot" style="background:#f97316"></i>ingeleverd</span>
                                    <span class="legend-item"><i class="legend-dot" style="background:#fbbf24"></i>bezig</span>
                                    <span class="legend-item"><i class="legend-dot" style="background:#d87bd8"></i>idee</span>
                                </div>
                            </div>
                        @else
                            <p class="spin-chart-empty">Het spindiagram verschijnt zodra werkprocessen aan jouw groep zijn gekoppeld.</p>
                        @endif
                    </section>
                @endif
            </div>
        @php
        $kerntaken = collect($processes)->groupBy('kerntaak');
        $requestedProcess = (string) request('werkproces', '');
        $requestedProcessData = collect($processes)->firstWhere('code', $requestedProcess);
        $selectedKerntaak = $requestedProcessData['kerntaak'] ?? (request('kerntaak') === 'K2' ? 'K2' : 'K1');
        if (!$kerntaken->has($selectedKerntaak)) $selectedKerntaak = $kerntaken->keys()->first() ?? 'K1';
        $activeProcessCode = $requestedProcessData['code'] ?? ($kerntaken->get($selectedKerntaak)?->first()['code'] ?? '');
        @endphp
        <div class="evidence-plan evidence-plan-shell" x-data="{ selectedKerntaak: @js($selectedKerntaak), selectedProcess: @js((string) $activeProcessCode) }">
            @if($portfolio && count($processes))
            <div class="process-layout">
                <div class="kerntaak-tabs" role="tablist" aria-label="Kies een kerntaak">
                    @foreach ($kerntaken as $kerntaak => $taskProcesses)
                    <button
                        type="button"
                        id="kerntaak-tab-{{ $kerntaak }}"
                        role="tab"
                        aria-controls="work-process-tabs-{{ $kerntaak }}"
                        :aria-selected="selectedKerntaak === '{{ $kerntaak }}'"
                        :class="selectedKerntaak === '{{ $kerntaak }}' ? 'kerntaak-tab is-active' : 'kerntaak-tab'"
                        @click="selectedKerntaak = '{{ $kerntaak }}'; selectedProcess = '{{ $taskProcesses->first()['code'] }}'; const url = new URL(window.location.href); url.searchParams.set('kerntaak', selectedKerntaak); url.searchParams.set('werkproces', selectedProcess); window.history.replaceState({}, '', url)">{{ $kerntaak }}</button>
                    @endforeach
                </div>
                <div class="process-main">
                    <div class="process-content">
                        @foreach ($kerntaken as $kerntaak => $taskProcesses)
                        <div class="process-tabs" id="work-process-tabs-{{ $kerntaak }}" role="tablist" aria-label="Werkprocessen {{ $kerntaak }}" x-show="selectedKerntaak === '{{ $kerntaak }}'" x-cloak>
                            @foreach ($taskProcesses as $process)
                            <button
                                type="button"
                                id="work-process-tab-{{ $process['code'] }}"
                                role="tab"
                                aria-controls="work-process-panel-{{ $process['code'] }}"
                                :aria-selected="selectedProcess === '{{ $process['code'] }}'"
                                :class="selectedProcess === '{{ $process['code'] }}' ? 'process-tab is-active' : 'process-tab'"
                                @click="selectedProcess = '{{ $process['code'] }}'; const url = new URL(window.location.href); url.searchParams.set('kerntaak', selectedKerntaak); url.searchParams.set('werkproces', selectedProcess); window.history.replaceState({}, '', url)">{{ $process['work_label'] }}</button>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    <section class="process-panel panel" aria-label="Geselecteerd werkproces">
                        <div class="section-title">
                            <div>
                                <p class="eyebrow">02 / BEWIJSSTUKKENPLAN</p>
                                <h2>Werkprocessen</h2>
                            </div><span class="count">Total bewijsstuken: {{ $portfolio->evidence->count() }}</span>
                        </div>
                        @foreach ($processes as $process)
                        @php($items = $portfolio->evidence->where('work_process', $process['code']))
                        <div
                            class="process-group"
                            id="work-process-panel-{{ $process['code'] }}"
                            role="tabpanel"
                            aria-labelledby="work-process-tab-{{ $process['code'] }}"
                            x-show="selectedProcess === '{{ $process['code'] }}'"
                            x-cloak>
                            <h3>{{ $process['kerntaak'] }}{{ $process['work_label'] }} — {{ $process['name'] }} <span class="process-count">{{ $items->count() }} bewijsstukken</span></h3>
                            @forelse ($items as $item)
                            <form method="POST" action="{{ route('evidence.update', $item) }}" class="plan-row" enctype="multipart/form-data" x-data="{ evidenceStatus: @js($item->status === 'nakijken' ? 'ingeleverd' : $item->status) }">@csrf @method('PATCH')
                                <div class="plan-number">{{ $item->item_number }}</div>
                                <div class="plan-main"><label class="evidence-name">Naam van bewijsstuk<input name="title" value="{{ $item->title }}" placeholder="Schrijf de naam van je bewijsstuk..." @disabled($portfolioLocked || $submitOnly || $item->status === 'akkoord')></label></div>
                                <div class="plan-fields">
                                    <label><span class="info-label">Status bewijsstuk</span><select name="status" x-model="evidenceStatus" required @disabled($portfolioLocked || $submitOnly || $item->status === 'akkoord')>
                                            @if(in_array($item->status, ['akkoord', 'niet akkoord'], true))<option value="{{ $item->status }}" selected>{{ ucfirst($item->status) }}</option>@endif
                                            <option value="idee" @selected($item->status === 'idee')>Idee</option>
                                            <option value="bezig" @selected($item->status === 'bezig')>Bezig</option>
                                            <option value="ingeleverd" @selected(in_array($item->status, ['ingeleverd', 'nakijken'], true))>Ingeleverd</option>
                                        </select></label>
                                    <p class="evidence-idea-visibility" x-cloak x-show="evidenceStatus === 'idee'">Je docent kan dit bewijsstuk en je notitie niet zien zolang de status op Idee staat. Kies ‘Ingeleverd’ wanneer je het wilt delen.</p>
                                </div>
                                <label class="plan-note">Notitie / idee<textarea name="note" rows="2" placeholder="Schrijf hier je idee of notitie..." @disabled($portfolioLocked || $submitOnly || $item->status === 'akkoord')>{{ $item->note }}</textarea></label><label class="submitted-where">Waar ingeleverd? <span>(niet verplicht)</span><input name="submitted_where" value="{{ $item->submitted_where }}" placeholder="Teams, Canvas of SharePoint" @disabled($portfolioLocked || $submitOnly || $item->status === 'akkoord')></label>
                                <div class="file-edit">
                                    @if($item->status === 'akkoord' || $portfolioLocked || $submitOnly)
                                    <strong>{{ $item->status === 'akkoord' ? 'Bestand vergrendeld na akkoord' : 'Portfolio is vergrendeld voor wijzigingen' }}</strong>
                                    @else
                                            <label>PDF-bestand wijzigen <span>(niet verplicht)</span><input type="file" name="file" accept=".pdf,application/pdf"></label>
                                    @endif
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
                                </div>
                                @if(!$portfolioLocked && !$submitOnly && $item->status !== 'akkoord')<button class="button small-button" type="submit">Wijzigingen opslaan</button>@endif
                            </form>
                            @if(in_array($item->status, ['akkoord', 'niet akkoord'], true))<p class="teacher-status-line"><strong>Docent status:</strong> {{ ucwords($item->status) }}</p>@endif
                            @if($item->feedback)<aside class="teacher-feedback"><h4>Notitie van {{ $item->feedback_author_name ?: 'beoordelaar' }}</h4><p>{{ $item->feedback }}</p></aside>@endif
                            @if($item->status !== 'akkoord' && !$portfolioLocked && !$submitOnly)
                            <div class="file-actions">
                                @if($item->file_path)<form method="POST" action="{{ route('evidence.file.destroy', $item) }}" class="delete-idea-form" data-confirm="Weet u zeker dat u dit bestand wilt verwijderen?">@csrf @method('DELETE')<button class="delete-idea" type="submit">Bestand verwijderen</button></form>@endif
                                <form method="POST" action="{{ route('evidence.destroy', $item) }}" class="delete-idea-form" data-confirm="Weet u zeker dat u dit bewijsstuk en het bijbehorende bestand wilt verwijderen?">@csrf @method('DELETE')<button class="evidence-delete-button" type="submit">Bewijsstuk verwijderen</button></form>
                            </div>
                            @endif
                            @empty
                            <p class="process-empty">Nog geen bewijsstuk toegevoegd.</p>
                            @endforelse
                            @if(!$portfolioLocked)
                            @if($submitOnly)<p class="process-empty">In de groep Herkansen kun je alleen nieuwe bewijsstukken inleveren.</p>@endif
                            <form method="POST" action="{{ route('evidence.store') }}" class="quick-add" enctype="multipart/form-data" x-data="{ newEvidenceStatus: '' }">@csrf
                                <input type="hidden" name="work_process" value="{{ $process['code'] }}">
                                <label>Naam bewijsstuk<input name="title" required placeholder="Naam van jouw bewijsstuk"></label>
                                <label>Type<select name="category" required>
                                        <option value="" disabled selected>Selecteer een type...</option>
                                        <option>Stageproject</option>
                                        <option>Schoolproject</option>
                                        <option>Keuzedeel</option>
                                        <option>Iets anders</option>
                                    </select></label>
                                <label>Status<select name="status" x-model="newEvidenceStatus" required>
                                        <option value="" disabled selected>Selecteer een status...</option>
                                        <option value="idee">Idee</option>
                                        <option value="bezig">Bezig</option>
                                        <option value="ingeleverd">Ingeleverd</option>
                                    </select></label>
                                <p class="evidence-idea-visibility" x-cloak x-show="newEvidenceStatus === 'idee'">Je docent kan dit bewijsstuk en je notitie niet zien zolang de status op Idee staat. Kies ‘Ingeleverd’ wanneer je het wilt delen.</p>
                                <label>Notitie / idee<textarea name="note" rows="2" placeholder="Schrijf hier je idee of notitie..."></textarea></label>
                                <label>PDF-bestand<input type="file" name="file" accept=".pdf,application/pdf"></label>
                                <button class="button tiny-button" type="submit">Bewijsstuk toevoegen</button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </section>
                </div>
            </div>
            @elseif($portfolio)
            <section class="process-panel panel">
                <div class="section-title">
                    <div>
                        <p class="eyebrow">02 / BEWIJSSTUKKENPLAN</p>
                        <h2>Werkprocessen</h2>
                    </div><span class="count">Totaal bewijsstukken: {{ $portfolio->evidence->count() }}</span>
                </div>
                @if($hasGroup)
                <p>Er zijn nog geen werkprocessen ingesteld voor jouw groep. Vraag je docent om ze toe te voegen via de groepsinstellingen.</p>
                @else
                <p>Je account is nog niet aan een lesgroep gekoppeld. De statusgroep NIEUW telt hiervoor niet mee. Vraag je docent of admin om je aan een lesgroep toe te voegen. Daarna verschijnen hier de werkprocessen en het spindiagram.</p>
                @endif
            </section>
            @else

            @endif

        </div>
    </div>
</x-app-layout>