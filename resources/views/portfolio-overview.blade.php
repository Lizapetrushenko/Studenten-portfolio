<x-app-layout headerTitle="Mijn portfolio">
    <div class="portal-shell">
            @if (session('status'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 10000)" x-show="show" role="status" aria-live="polite" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0" class="fixed bottom-6 right-6 z-50 w-[calc(100%-3rem)] max-w-sm pointer-events-auto">
                    <div class="bg-white rounded-xl shadow-2xl border border-green-100 border-l-4 border-l-green-500 overflow-hidden">
                        <div class="p-4 flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-gray-900">Gelukt</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ session('status') }}</p>
                            </div>
                            <button @click="show = false" aria-label="Melding sluiten" class="text-gray-400 hover:text-gray-600 transition">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-green-500 to-emerald-500 animate-pulse"></div>
                    </div>
                </div>
            @endif
            
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
                            <form method="POST" action="{{ route('portfolio.destroy', $portfolio) }}" class="mt-4" onsubmit="return confirm('Weet je zeker dat je je portfolio en alle bewijsstukken wilt verwijderen?')">@csrf @method('DELETE')<button class="delete-idea" type="submit">Portfolio verwijderen</button></form>
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
                                    @click="selectedKerntaak = '{{ $kerntaak }}'; selectedProcess = '{{ $taskProcesses->first()['code'] }}'; const url = new URL(window.location.href); url.searchParams.set('kerntaak', selectedKerntaak); url.searchParams.set('werkproces', selectedProcess); window.history.replaceState({}, '', url)"
                                >{{ $kerntaak }}</button>
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
                                            @click="selectedProcess = '{{ $process['code'] }}'; const url = new URL(window.location.href); url.searchParams.set('kerntaak', selectedKerntaak); url.searchParams.set('werkproces', selectedProcess); window.history.replaceState({}, '', url)"
                                        >{{ $process['work_label'] }}</button>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    <section class="process-panel panel" aria-label="Geselecteerd werkproces">
                    <div class="section-title"><div><p class="eyebrow">02 / BEWIJSSTUKKENPLAN</p><h2>Werkprocessen</h2></div><span class="count">Total bewijsstuken: {{ $portfolio->evidence->count() }}</span></div>
                    @foreach ($processes as $process)
                        @php($items = $portfolio->evidence->where('work_process', $process['code']))
                        <div
                            class="process-group"
                            id="work-process-panel-{{ $process['code'] }}"
                            role="tabpanel"
                            aria-labelledby="work-process-tab-{{ $process['code'] }}"
                            x-show="selectedProcess === '{{ $process['code'] }}'"
                            x-cloak
                        ><h3>{{ $process['kerntaak'] }}{{ $process['work_label'] }} — {{ $process['name'] }} <span class="process-count">{{ $items->count() }} bewijsstukken</span></h3>
                            @forelse ($items as $item)
                                    <form method="POST" action="{{ route('evidence.update', $item) }}" class="plan-row" enctype="multipart/form-data">@csrf @method('PATCH')
                                    <div class="plan-number">{{ $item->item_number }}</div>
                                    <div class="plan-main"><label class="evidence-name">Naam van bewijsstuk<input name="title" value="{{ $item->title }}" placeholder="Schrijf de naam van je bewijsstuk..." @disabled($portfolioLocked || $submitOnly || $item->status === 'akkoord')></label><label class="idea-label">Idee van bewijsstuk<input name="idea" value="{{ $item->idea }}" placeholder="Schrijf je idee..." @disabled($portfolioLocked || $submitOnly || $item->status === 'akkoord')></label><div class="idea-actions">@if(!$portfolioLocked && !$submitOnly && $item->status !== 'akkoord')<button class="button tiny-button" type="submit">Opslaan</button>@endif</div></div>
                                    <div class="plan-fields"><label><span class="info-label">Status bewijsstuk</span><select name="status" @disabled($portfolioLocked || $submitOnly || $item->status === 'akkoord')><option value="idee" @selected($item->status === 'idee')>Idee</option><option value="bezig" @selected($item->status === 'bezig')>Bezig</option><option value="ingeleverd" @selected($item->status === 'ingeleverd')>Ingeleverd</option></select></label><label class="check-label"><input type="checkbox" name="is_completed" value="1" @checked($item->is_completed) @disabled($portfolioLocked || $submitOnly || $item->status === 'akkoord')> Gemaakt</label></div>
                                    <label class="plan-note">Notitie<textarea name="note" rows="2" placeholder="Notitie..." @disabled($portfolioLocked || $submitOnly || $item->status === 'akkoord')>{{ $item->note }}</textarea></label><label class="submitted-where">Waar ingeleverd? <span>(niet verplicht)</span><input name="submitted_where" value="{{ $item->submitted_where }}" placeholder="Teams, Canvas of SharePoint" @disabled($portfolioLocked || $submitOnly || $item->status === 'akkoord')></label>
                                    <div class="file-edit">
                                        @if($item->status === 'akkoord' || $portfolioLocked || $submitOnly)
                                            <strong>{{ $item->status === 'akkoord' ? 'Bestand vergrendeld na akkoord' : 'Portfolio is vergrendeld voor wijzigingen' }}</strong>
                                        @else
                                            <label>Bestand wijzigen <span>(niet verplicht)</span><input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"></label>
                                        @endif
                                        @if($item->file_path)<div class="flex gap-4"><a href="{{ route('evidence.open', $item) }}" target="_blank" rel="noopener">Bestand openen</a><a href="{{ route('evidence.download', $item) }}">Bestand downloaden</a></div>@endif
                                    </div>
                                    @if(!$portfolioLocked && !$submitOnly && $item->status !== 'akkoord')<button class="button small-button" type="submit">Wijzigingen opslaan</button>@endif
                                </form>
                                @if($item->feedback)<p class="plan-note"><strong>Notitie docent:</strong> {{ $item->feedback }}</p>@endif
                                @if($item->status !== 'akkoord' && !$portfolioLocked && !$submitOnly)
                                    <div class="file-actions">
                                        @if($item->file_path)<form method="POST" action="{{ route('evidence.file.destroy', $item) }}" class="delete-idea-form" onsubmit="return confirm('Dit bestand verwijderen?')">@csrf @method('DELETE')<button class="delete-idea" type="submit">Bestand verwijderen</button></form>@endif
                                        <form method="POST" action="{{ route('evidence.destroy', $item) }}" class="delete-idea-form" onsubmit="return confirm('Dit bewijsstuk en het bijbehorende bestand verwijderen?')">@csrf @method('DELETE')<button class="delete-idea" type="submit">Bewijsstuk verwijderen</button></form>
                                    </div>
                                @endif
                                @if($item->idea && !$portfolioLocked && !$submitOnly && $item->status !== 'akkoord')<form method="POST" action="{{ route('evidence.idea.destroy', $item) }}" class="delete-idea-form">@csrf @method('DELETE')<button class="delete-idea" type="submit">Idee verwijderen</button></form>@endif
                            @empty
                                <p class="process-empty">Nog geen bewijsstuk toegevoegd.</p>
                            @endforelse
                            @if(!$portfolioLocked)
                            @if($submitOnly)<p class="process-empty">In de groep Herkansen kun je alleen nieuwe bewijsstukken inleveren.</p>@endif
                            <form method="POST" action="{{ route('evidence.store') }}" class="quick-add" enctype="multipart/form-data">@csrf
                                <input type="hidden" name="work_process" value="{{ $process['code'] }}">
                                <label>Naam bewijsstuk<input name="title" required placeholder="Naam van jouw bewijsstuk"></label>
                                <label>Type<select name="category"><option>Stageproject</option><option>Schoolproject</option><option>Keuzedeel</option><option>Iets anders</option></select></label>
                                <label>Status<select name="status"><option value="idee">Idee</option><option value="bezig">Bezig</option><option value="ingeleverd" selected>Ingeleverd</option></select></label>
                                <label>Bestand <span>(PDF, Word of afbeelding; niet verplicht)</span><input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"></label>
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
                        <div class="section-title"><div><p class="eyebrow">02 / BEWIJSSTUKKENPLAN</p><h2>Werkprocessen</h2></div><span class="count">Totaal bewijsstukken: {{ $portfolio->evidence->count() }}</span></div>
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


