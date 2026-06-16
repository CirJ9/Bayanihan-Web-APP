/* LINE GRAPH */
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Requests',
            data: [12, 18, 10, 20, 25, 30, 22],
            borderColor: '#1e3a8a',
            backgroundColor: 'rgba(30,58,138,0.15)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

/* PIE GRAPH */
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: ['Completed', 'Pending', 'Cancelled'],
        datasets: [{
            data: [64, 15, 6],
            backgroundColor: ['#22c55e', '#facc15', '#ef4444']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});