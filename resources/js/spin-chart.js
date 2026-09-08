document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('.spin-chart-container-small');
    const ChartLibrary = window.Chart;

    if (!container || !ChartLibrary) return;

    const chartData = JSON.parse(container.getAttribute('data-chart-data'));
    const context = document.getElementById('spinChart')?.getContext('2d');

    if (!context) return;

    const processCodes = Object.keys(chartData);
    const processLabels = JSON.parse(container.getAttribute('data-chart-labels') || '[]');
    const statusColors = {
        akkoord: '#22c55e',
        ingeleverd: '#f97316',
        'in proces': '#fbbf24',
        'niet akkoord': '#dc2626',
        idee: '#d87bd8'
    };
    const statuses = ['akkoord', 'ingeleverd', 'in proces', 'niet akkoord', 'idee'];
    const datasets = statuses.map(status => ({
        label: status.charAt(0).toUpperCase() + status.slice(1),
        data: processCodes.map(code => chartData[code][status] || 0),
        borderColor: statusColors[status],
        backgroundColor: statusColors[status] + '33',
        pointBackgroundColor: statusColors[status],
        borderWidth: 1.5,
        pointRadius: 2.5,
        pointHoverRadius: 4,
        tension: 0.3,
        fill: true
    }));

    new ChartLibrary(context, {
        type: 'radar',
        data: { labels: processLabels.length ? processLabels : processCodes, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 5,
                    ticks: { stepSize: 1, font: { size: 9 } },
                    grid: { color: '#e5e7eb' }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: context => `${context.label} - ${context.dataset.label}: ${context.parsed.r} bewijsstukken`
                    }
                }
            }
        }
    });
});
