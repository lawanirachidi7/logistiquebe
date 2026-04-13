import Chart from 'chart.js/auto';

window.renderStatCharts = function() {
    // Bar chart for top 5 buses
    const busLabels = window.topBusLabels || [];
    const busData = window.topBusData || [];
    const busCtx = document.getElementById('topBusChart');
    if (busCtx && busLabels.length && busData.length) {
        new Chart(busCtx, {
            type: 'bar',
            data: {
                labels: busLabels,
                datasets: [{
                    label: 'Voyages',
                    data: busData,
                    backgroundColor: '#198754',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Top 5 Bus les plus utilisés' }
                }
            }
        });
    }

    // Pie chart for top 5 conducteurs
    const conducteurLabels = window.topConducteurLabels || [];
    const conducteurData = window.topConducteurData || [];
    const conducteurCtx = document.getElementById('topConducteurChart');
    if (conducteurCtx && conducteurLabels.length && conducteurData.length) {
        new Chart(conducteurCtx, {
            type: 'pie',
            data: {
                labels: conducteurLabels,
                datasets: [{
                    label: 'Voyages',
                    data: conducteurData,
                    backgroundColor: [
                        '#0d6efd', '#6610f2', '#6f42c1', '#198754', '#ffc107'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'Répartition des voyages par conducteur (Top 5)' }
                }
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', window.renderStatCharts);