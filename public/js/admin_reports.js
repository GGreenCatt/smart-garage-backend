import { REVENUE_DATA, REPAIR_STATS, TOP_PARTS, EFFICIENCY_METRICS } from './admin_reports_data.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Render Revenue Chart (Line)
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: REVENUE_DATA,
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1e293b',
                    bodyColor: '#475569',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { color: '#94a3b8' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8' }
                }
            }
        }
    });

    // 2. Render Status Chart (Doughnut)
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: REPAIR_STATS,
        options: {
            maintainAspectRatio: false,
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    // 3. Render Top Parts Chart (Bar)
    const partsCtx = document.getElementById('partsChart').getContext('2d');
    new Chart(partsCtx, {
        type: 'bar',
        data: TOP_PARTS,
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { display: false },
                x: { grid: { display: false }, ticks: { color: '#64748b' } }
            }
        }
    });

    // 4. Update KPI Text (Simulated Dynamic Data)
    document.getElementById('kpi-rating').innerText = EFFICIENCY_METRICS.customerSatisfaction;
    document.getElementById('kpi-time').innerText = EFFICIENCY_METRICS.avgRepairTime;
});
