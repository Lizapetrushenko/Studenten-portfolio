<x-app-layout headerTitle="Mijn portfolio">
    <div class="portal-shell">
            @if (session('status'))<div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 4000)" x-show="visible" x-transition.opacity class="notice flash-message">{{ session('status') }}</div>@endif
            
            <div class="portfolio-sections">
                <section class="panel portfolio-panel">
                    <div class="section-title"><div><p class="eyebrow">01 / PROFIEL</p><h2>Portfolio-informatie</h2></div><span class="tag">{{ $portfolio ? 'Actief' : 'Start hier' }}</span></div>
                    
                    @if($portfolio)
                        <!-- Portfolio Bestaande Info -->
                        <div class="portfolio-info">
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
                            <div class="portfolio-actions">
                                <form method="POST" action="{{ route('portfolio.destroy', $portfolio) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button delete-button" onclick="return confirm('Weet je zeker dat je dit portfolio wilt verwijderen? Je kunt dan een nieuw portfolio aanmaken.')">Portfolio verwijderen</button>
                                </form>
                            </div>
                        </div>
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
                            <div class="spin-chart-container-small" data-chart-data="{{ htmlspecialchars($spinChartData) }}">
                                <canvas id="spinChart"></canvas>
                            </div>
                            <div class="spin-legend-small">
                                <div class="legend-item"><span class="legend-dot akkoord"></span><span class="legend-text">Akkoord</span></div>
                                <div class="legend-item"><span class="legend-dot ingeleverd"></span><span class="legend-text">Ingeleverd</span></div>
                                <div class="legend-item"><span class="legend-dot in-proces"></span><span class="legend-text">In proces</span></div>
                                <div class="legend-item"><span class="legend-dot niet-akkoord"></span><span class="legend-text">Niet akkoord</span></div>
                            </div>
                        </div>
                    </section>
                @endif
            </div>

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


@if($portfolio && $spinChartData)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.spin-chart-container-small');
        const chartDataJson = container.getAttribute('data-chart-data');
        const chartData = JSON.parse(chartDataJson);
        const ctx = document.getElementById('spinChart')?.getContext('2d');
        
        if (!ctx) return;

        const processCodes = Object.keys(chartData);
        const statusColors = {
            'akkoord': '#22c55e',
            'ingeleverd': '#f97316',
            'in proces': '#fbbf24',
            'in-proces': '#fbbf24',
            'niet akkoord': '#dc2626',
            'idee': '#d87bd8'
        };

        const datasets = [];
        const statuses = ['akkoord', 'ingeleverd', 'in proces', 'niet akkoord', 'idee'];

        statuses.forEach(status => {
            const data = processCodes.map(code => {
                const count = chartData[code][status] || 0;
                return count;
            });

            datasets.push({
                label: status.charAt(0).toUpperCase() + status.slice(1),
                data: data,
                borderColor: statusColors[status],
                backgroundColor: statusColors[status] + '33',
                pointBackgroundColor: statusColors[status],
                borderWidth: 1.5,
                pointRadius: 2.5,
                pointHoverRadius: 4,
                tension: 0.3,
                fill: true
            });
        });

        const chart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: processCodes,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            stepSize: 1,
                            font: { size: 9 }
                        },
                        grid: {
                            color: '#e5e7eb'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.r + ' bewijsstukken';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endif
