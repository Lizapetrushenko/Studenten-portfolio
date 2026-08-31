<x-app-layout>
    <x-slot name="header">
            <div class="portal-header"><span class="eyebrow">STUDENTENPORTAAL</span><h1>Mijn portfolio</h1></div>
    </x-slot>

        <div class="portal-shell">
            @if (session('status'))<div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 4000)" x-show="visible" x-transition.opacity class="notice flash-message">{{ session('status') }}</div>@endif
            <section class="intro-grid">
                <div><p class="eyebrow">JOUW BEWIJSSTUKKENPLAN</p><h2>Werkproces voor werkproces.</h2><p class="lead">Schrijf per nummer je idee van bewijsstuk en notitie op. Markeer daarna wat klaar, ingeleverd of akkoord is.</p></div>
                <div class="profile-mark">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            </section>

            <section class="panel portfolio-panel">
                <div class="section-title"><div><p class="eyebrow">01 / PROFIEL</p><h2>Portfolio-informatie</h2></div><span class="tag">{{ $portfolio ? 'Actief' : 'Start hier' }}</span></div>
                <form method="POST" action="{{ route('portfolio.store') }}" class="form-grid portfolio-form">@csrf
                    <label>Projectnaam<input name="title" value="{{ old('title', $portfolio?->title) }}" placeholder="Schrijf zelf je projectnaam" required></label>
                    <label>Opleiding<input name="study" value="{{ old('study', $portfolio?->study) }}" placeholder="Bijv. Software Development"></label>
                    <label class="full">Korte introductie<textarea name="bio" rows="3" placeholder="Waar ben je trots op?">{{ old('bio', $portfolio?->bio) }}</textarea></label>
                    <div class="full"><button class="button" type="submit">Portfolio opslaan</button></div>
                </form>
            </section>

            <section class="evidence-plan panel">
                <div class="section-title"><div><p class="eyebrow">02 / BEWIJSSTUKKENPLAN</p><h2>Werkprocessen</h2></div><span class="count">{{ $portfolio?->evidence->count() ?? 0 }}</span></div>
                <div class="status-legend"><span class="status-dot akkoord"></span> Akkoord <span class="status-dot ingeleverd"></span> Ingeleverd <span class="status-dot in-proces"></span> In proces <span class="status-dot niet-bekeken"></span> Niet bekeken <span class="status-dot niet-akkoord"></span> Niet akkoord</div>
                @if($portfolio)
                    @foreach ($processes as $process)
                        @php($items = $portfolio->evidence->where('work_process', $process['label']))
                        <div class="process-group"><h3>{{ $process['label'] }} <span class="process-count">{{ $items->count() }} bewijsstukken</span></h3>
                            @forelse ($items as $item)
                                    <form method="POST" action="{{ route('evidence.update', $item) }}" class="plan-row" enctype="multipart/form-data">@csrf @method('PATCH')
                                    <div class="plan-number">{{ $item->item_number }}</div>
                                    <div class="plan-main"><label class="evidence-name">Naam van bewijsstuk<input name="title" value="{{ $item->title }}" placeholder="Schrijf de naam van je bewijsstuk..."></label><label class="idea-label">Idee van bewijsstuk<input name="idea" value="{{ $item->idea }}" placeholder="Schrijf je idee..."></label><div class="idea-actions"><button class="button tiny-button" type="submit">Opslaan</button></div></div>
                                    <div class="plan-fields"><label>Status<select name="status" class="status-{{ str_replace(' ', '-', $item->status ?? '') }}"><option value="">Kies status</option><option value="niet bekeken" @selected($item->status === 'niet bekeken')>Niet bekeken</option><option value="in proces" @selected($item->status === 'in proces')>In proces</option><option value="ingeleverd" @selected($item->status === 'ingeleverd')>Ingeleverd</option><option value="akkoord" @selected($item->status === 'akkoord')>Akkoord</option><option value="niet akkoord" @selected($item->status === 'niet akkoord')>Niet akkoord</option><option value="moet aangepast worden" @selected($item->status === 'moet aangepast worden')>Moet aangepast worden</option></select></label><label class="check-label"><input type="checkbox" name="is_completed" value="1" @checked($item->is_completed)> Gemaakt</label></div>
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
        </div>
</x-app-layout>
