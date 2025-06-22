document.addEventListener('DOMContentLoaded', function() {
    // --- Existing Dropdown Logic ---
    const userDropdownToggle = document.getElementById('userDropdownToggle');
    const userDropdownMenu = document.getElementById('userDropdownMenu');

    // Toggle the dropdown menu visibility when the button is clicked
    userDropdownToggle.addEventListener('click', function() {
        userDropdownMenu.classList.toggle('show');
    });

    // Close the dropdown if the user clicks outside of it
    document.addEventListener('click', function(event) {
        if (!userDropdownToggle.contains(event.target) && !userDropdownMenu.contains(event.target)) {
            userDropdownMenu.classList.remove('show');
        }
    });

    // Optional: Add functionality for Top Up and Log Out buttons (for demonstration)
    const topUpButton = userDropdownMenu.querySelector('.top-up-button');
    const logoutButton = userDropdownMenu.querySelector('.logout-button');

    topUpButton.addEventListener('click', function() {
        alert('Top Up functionality would go here!');
        // In a real application, this would redirect to a top-up page or open a modal.
    });

    logoutButton.addEventListener('click', function() {
        alert('Logging out...');
        // In a real application, this would clear session data and redirect to a login page.
    });

    // --- END Existing Dropdown Logic ---


    // --- NEW Chart.js Implementations ---

    // Common Chart Configuration Options (for consistency)
    const commonChartOptions = {
        responsive: true,
        maintainAspectRatio: false, // Allows you to control height with CSS
        plugins: {
            legend: {
                labels: {
                    color: 'var(--text-color)' // Text color for legend
                }
            }
        },
        scales: {
            x: {
                grid: {
                    color: 'var(--border-color)' // Grid line color
                },
                ticks: {
                    color: 'var(--text-color)' // X-axis label color
                }
            },
            y: {
                grid: {
                    color: 'var(--border-color)'
                },
                ticks: {
                    color: 'var(--text-color)' // Y-axis label color
                }
            }
        }
    };

    // 1. Wagered Over Time Chart (Line Chart)
    const wageredOverTimeCtx = document.getElementById('wageredOverTimeChart').getContext('2d');
    new Chart(wageredOverTimeCtx, {
        type: 'line',
        data: {
            labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
            datasets: [{
                label: 'Wagered Amount ($)',
                data: [150, 220, 180, 300, 250, 350, 280], // Example data
                borderColor: 'var(--accent-green)',
                backgroundColor: 'rgba(118, 185, 0, 0.2)', // Semi-transparent green
                tension: 0.3, // Smooth the line
                fill: true
            }]
        },
        options: {
            ...commonChartOptions, // Apply common options
            plugins: {
                ...commonChartOptions.plugins,
                title: {
                    display: true,
                    text: 'Daily Wagered Amount',
                    color: 'var(--text-color)',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });

    // 2. Bets by Game Type Chart (Pie Chart)
    const betsByGameTypeCtx = document.getElementById('betsByGameTypeChart').getContext('2d');
    new Chart(betsByGameTypeCtx, {
        type: 'pie',
        data: {
            labels: ['Blackjack', 'Slots', 'Roulette', 'Poker', 'Sports Betting'],
            datasets: [{
                data: [30, 25, 20, 15, 10], // Example data (percentages or counts)
                backgroundColor: [
                    'rgba(118, 185, 0, 0.8)', // NVIDIA Green
                    'rgba(0, 123, 255, 0.8)', // Blue
                    'rgba(220, 53, 69, 0.8)', // Red
                    'rgba(255, 193, 7, 0.8)',  // Yellow
                    'rgba(23, 162, 184, 0.8)' // Cyan
                ],
                borderColor: 'var(--bg-dark)', // Dark border to separate slices
                borderWidth: 2
            }]
        },
        options: {
            ...commonChartOptions, // Apply common options
            plugins: {
                ...commonChartOptions.plugins,
                title: {
                    display: true,
                    text: 'Distribution of Bets by Game Type',
                    color: 'var(--text-color)',
                    font: {
                        size: 16
                    }
                }
            },
            scales: { // Hide scales for pie chart
                x: { display: false },
                y: { display: false }
            }
        }
    });
});