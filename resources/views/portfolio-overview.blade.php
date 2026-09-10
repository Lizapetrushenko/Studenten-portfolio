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
                <section class="panel portfolio-panel" x-data="{ editing: false }">
                    <div class="section-title"><div><p class="eyebrow">01 / PROFIEL</p><h2>Portfolio-informatie</h2></div><div class="flex items-center gap-3"><span class="tag">{{ $portfolio ? 'Actief' : 'Start hier' }}</span>@if($portfolio)<button type="button" class="button tiny-button" @click.prevent="editing = !editing" x-text="editing ? 'Annuleren' : 'Aanpassen'"></button>@endif</div></div>
                    
                    @if($portfolio)
                        <!-- Portfolio Bestaande Info -->
                        <div class="portfolio-info" x-show="!editing">
                            <div class="info-item">
                                <label class="info-label">Projectnaam:</label>
                                <p class="info-value">{{ $portfolio->title }}</p>
                            </div>
                            <div class="info-item">
                                <label class="info-label">Opleiding:</label>
                                <p class="info-value">{{ $portfolio->study }}</p>
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
                    @else
                        <!-- Portfolio Invulformulier -->
                        <form method="POST" action="{{ route('portfolio.store') }}" class="form-grid portfolio-form">@csrf
                            <label>Projectnaam<input name="title" value="{{ old('title') }}" placeholder="Schrijf zelf je projectnaam" required></label>
                            <label>Opleiding<select name="study" required><option value="">Kies opleiding</option><option value="BOL" @selected(old('study') === 'BOL')>BOL</option><option value="BBL" @selected(old('study') === 'BBL')>BBL</option><option value="Flex" @selected(old('study') === 'Flex')>Flex</option></select></label>
                            <label class="full">Korte introductie<textarea name="bio" rows="3" placeholder="Waar ben je trots op?">{{ old('bio') }}</textarea></label>
                            <div class="full"><button class="button" type="submit">Portfolio opslaan</button></div>
                        </form>
                    @endif
                </section>

                @if($portfolio)
                    <!-- Spin Diagram Widget Section -->
                    <section class="panel spin-chart-panel">
                        <div class="section-title"><div><p class="eyebrow">03 / OVERZICHT</p><h2>Van je werkzaamheden</h2></div></div>
                        <div class="spin-chart-widget-small">
                            <div class="spin-chart-container-small" data-chart-data="{{ $spinChartData }}" data-chart-labels="{{ json_encode(collect($processes)->pluck('label')->values()) }}">
                                <canvas id="spinChart"></canvas>
                            </div>
                            <div class="status-summary">
                                <div class="summary-item akkoord"><strong>{{ $overallStatusCounts['akkoord'] ?? 0 }}</strong><span>Akkoord</span></div>
                                <div class="summary-item niet-akkoord"><strong>{{ $overallStatusCounts['niet akkoord'] ?? 0 }}</strong><span>Niet akkoord</span></div>
                                <div class="summary-item ingeleverd"><strong>{{ $overallStatusCounts['ingeleverd'] ?? 0 }}</strong><span>Ingeleverd</span></div>
                                <div class="summary-item in-proces"><strong>{{ $overallStatusCounts['in proces'] ?? 0 }}</strong><span>In proces</span></div>
                                <div class="summary-item idee"><strong>{{ $overallStatusCounts['idee'] ?? 0 }}</strong><span>Idee</span></div>
                            </div>
                        </div>
                    </section>
                @endif
            </div>

            <section class="evidence-plan panel">
                <div class="section-title"><div><p class="eyebrow">02 / BEWIJSSTUKKENPLAN</p><h2>Werkprocessen</h2></div><span class="count">Total bewijsstuken: {{ $portfolio?->evidence->count() ?? 0 }}</span></div>
                @if($portfolio)
                    @foreach ($processes as $process)
                        @php($items = $portfolio->evidence->where('work_process', $process['code']))
                        <div class="process-group"><h3>{{ $process['code'] }}. <form method="POST" action="{{ route('work-process.update', $portfolio->workProcesses->firstWhere('number', $process['code'])) }}" class="inline-process-form">@csrf @method('PATCH')<input name="name" value="{{ $process['name'] }}" aria-label="Naam van werkproces"><button class="button tiny-button" type="submit">Opslaan</button></form> <span class="process-count">{{ $items->count() }} bewijsstukken</span></h3>
                            @forelse ($items as $item)
                                    <form method="POST" action="{{ route('evidence.update', $item) }}" class="plan-row" enctype="multipart/form-data">@csrf @method('PATCH')
                                    <div class="plan-number">{{ $item->item_number }}</div>
                                    <div class="plan-main"><label class="evidence-name">Naam van bewijsstuk<input name="title" value="{{ $item->title }}" placeholder="Schrijf de naam van je bewijsstuk..."></label><label class="idea-label">Idee van bewijsstuk<input name="idea" value="{{ $item->idea }}" placeholder="Schrijf je idee..."></label><div class="idea-actions"><button class="button tiny-button" type="submit">Opslaan</button></div></div>
                                    <div class="plan-fields"><label>Status<select name="status" class="status-{{ str_replace(' ', '-', $item->status ?? '') }}"><option value="">Kies status</option><option value="in proces" @selected($item->status === 'in proces')>In proces</option><option value="ingeleverd" @selected($item->status === 'ingeleverd')>Ingeleverd</option><option value="akkoord" @selected($item->status === 'akkoord')>Akkoord</option><option value="niet akkoord" @selected($item->status === 'niet akkoord')>Niet akkoord</option><option value="idee" @selected($item->status === 'idee')>Idee</option></select></label><label class="check-label"><input type="checkbox" name="is_completed" value="1" @checked($item->is_completed)> Gemaakt</label></div>
                                    <label class="plan-note">Notitie<textarea name="note" rows="2" placeholder="Notitie...">{{ $item->note }}</textarea></label><label class="submitted-where">Waar ingeleverd? <span>(niet verplicht)</span><input name="submitted_where" value="{{ $item->submitted_where }}" placeholder="Teams, Canvas of SharePoint"></label><label class="file-edit">Bestand wijzigen <span>(niet verplicht)</span><input type="file" name="file" accept=".pdf,.doc,.docx"></label><button class="button small-button" type="submit">Wijzigen opslaan</button>
                                </form>
                                @if($item->idea)<form method="POST" action="{{ route('evidence.idea.destroy', $item) }}" class="delete-idea-form">@csrf @method('DELETE')<button class="delete-idea" type="submit">Idee verwijderen</button></form>@endif
                            @empty
                                <p class="process-empty">Nog geen bewijsstuk toegevoegd.</p>
                            @endforelse
                            <form method="POST" action="{{ route('evidence.store') }}" class="quick-add" enctype="multipart/form-data">@csrf
                                <input type="hidden" name="work_process" value="{{ $process['code'] }}">
                                <label>Naam bewijsstuk<input name="title" required placeholder="Naam van jouw bewijsstuk"></label>
                                <label>Type<select name="category"><option>Stageproject</option><option>Schoolproject</option><option>Keuzedeel</option><option>Iets anders</option></select></label>
                                <label>Bestand <span>(Word of PDF, niet verplicht)</span><input type="file" name="file" accept=".doc,.docx,.pdf"></label>
                                <button class="button tiny-button" type="submit">Bewijsstuk toevoegen</button>
                            </form>
                        </div>
                    @endforeach
                    
                @else
                
                @endif
                
            </section>
            <form method="POST" action="{{ route('work-process.store') }}" class="process-add-form">@csrf
                        <div class="process-add-copy"><strong>Nieuw werkproces aanmaken</strong><span>Het volgende nummer wordt automatisch toegevoegd.</span></div>
                        <label>Naam van werkproces<input name="name" required maxlength="120" placeholder="Bijvoorbeeld: Reflecteren"></label>
                        <button class="button" type="submit">+ Werkproces toevoegen</button>
                    </form>
        </div>
</x-app-layout>


