/**
 * SENA APÍCOLA • Dashboard Administrativo
 * Inicialización de la gráfica de Producción de Miel (Chart.js)
 */
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('honeyChart');
    if (!ctx) return;

    const chartCtx = ctx.getContext('2d');

    // Gradiente suave verde esmeralda para el área
    const gradient = chartCtx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(46, 125, 50, 0.18)');
    gradient.addColorStop(1, 'rgba(46, 125, 50, 0.00)');

    let labels = ['Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep'];
    let data = [60, 84, 98, 115, 96, 135];

    try {
        if (ctx.dataset.labels) {
            labels = JSON.parse(ctx.dataset.labels);
        }
        if (ctx.dataset.values) {
            data = JSON.parse(ctx.dataset.values);
        }
    } catch (e) {
        console.warn('Usando valores predeterminados para la gráfica apícola:', e);
    }

    new Chart(chartCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Producción (kg)',
                data: data,
                borderColor: '#2e7d32',
                borderWidth: 2.5,
                backgroundColor: gradient,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#2e7d32',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4.5,
                pointHoverRadius: 6.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e3d2f',
                    titleColor: '#ffffff',
                    bodyColor: '#a7f3d0',
                    padding: 10,
                    borderRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function (context) {
                            return context.parsed.y + ' kg cosechados';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            family: "'Plus Jakarta Sans', sans-serif",
                            size: 11
                        }
                    }
                },
                y: {
                    min: 0,
                    max: 200,
                    ticks: {
                        stepSize: 50,
                        color: '#94a3b8',
                        font: {
                            family: "'Plus Jakarta Sans', sans-serif",
                            size: 11
                        }
                    },
                    grid: {
                        color: '#f1f5f9'
                    },
                    border: {
                        dash: [4, 4]
                    }
                }
            }
        }
    });
});
