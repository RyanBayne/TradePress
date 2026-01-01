/**
 * Fundamental Analysis Charts
 * 
 * Uses Chart.js to render fundamental analysis charts
 */
jQuery(document).ready(function($) {
    'use strict';
    
    // Revenue & EPS Chart
    const revenueEpsChart = document.getElementById('revenue-eps-chart');
    if (revenueEpsChart) {
        const ctx = revenueEpsChart.getContext('2d');
        
        // Get data from data attributes
        const labels = JSON.parse(revenueEpsChart.getAttribute('data-labels'));
        const revenueData = JSON.parse(revenueEpsChart.getAttribute('data-revenue'));
        const epsData = JSON.parse(revenueEpsChart.getAttribute('data-eps'));
        
        // Create chart
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Quarterly Revenue (Billions)',
                        data: revenueData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        yAxisID: 'y-revenue',
                    },
                    {
                        label: 'EPS',
                        data: epsData,
                        type: 'line',
                        fill: false,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        yAxisID: 'y-eps',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    'y-revenue': {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (Billions)'
                        }
                    },
                    'y-eps': {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'EPS ($)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });
    }
});
