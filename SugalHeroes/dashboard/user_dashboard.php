<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Gambling Project</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/newlogo.png" alt="Your Brand Logo" id="sidebarLogo">
            </div>
            <nav class="main-nav">
                <ul>
                    <li>
                        <a href="#" class="nav-item active" id="dashboardLink"> <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-item" id="statisticsLink"> <i class="fas fa-chart-bar"></i> Statistics
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-item" id="transactionsLink"> <i class="fas fa-history"></i> Transactions
                        </a>
                    </li>
                    <li>
                        <a href="user_settings.php" class="nav-item" id="settingsLink"> <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <li>
                        <a href="user_help.php" class="nav-item" id="helpLink"> <i class="fas fa-question-circle"></i> Help
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="main-content" id="topOfDashboard">
            <header class="header">
                <a href="../homepage/index.php" class="home-button"><i class="fas fa-chevron-left"></i> Home</a>
                <div class="user-account-dropdown">
                    <button class="dropdown-toggle" id="userDropdownToggle">
                        <i class="fas fa-user-circle"></i> <span id="headerUsername">Username</span>
                    </button>
                    <div class="dropdown-menu" id="userDropdownMenu">
                        <div class="account-balance" id="headerBalanceDisplay">Balance: $0.00</div>
                        <button class="top-up-button" id="topUpBtn">Top Up</button>
                        <button class="logout-button" id="logoutBtn">Log Out</button>
                    </div>
                </div>
            </header>

            <section class="dashboard-display">
                <h1>Welcome, <span id="welcomeUsername">User</span>!</h1>
                <p>Here you can manage your account and view your activities.</p>

                <div class="balance-section">
    
                        
                    <div class="balance-card">
                        <h3>SugalCoin Balance (SGC)</h3>
                        <p id="sgcBalanceDisplay">Loading...</p>
                    </div>
                </div>

                <div class="betting-history-window">
                    <h2><i class="fas fa-history"></i> Betting History</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Game</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Outcome/Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsTableBody">
                            <tr><td colspan="5">Loading transactions...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="statistics-window">
                    <h2><i class="fas fa-chart-bar"></i> Your Betting Statistics</h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Total Bets Placed</h3>
                            <p class="stat-value" id="totalBets">...</p>
                        </div>
                        <div class="stat-card">
                            <h3>Total Wagered</h3>
                            <p class="stat-value" id="totalWagered">...</p>
                        </div>
                        <div class="stat-card">
                            <h3>Net Profit/Loss</h3>
                            <p class="stat-value" id="netProfitLoss">...</p>
                        </div>
                        <div class="stat-card">
                            <h3>Win Rate</h3>
                            <p class="stat-value" id="winRate">...</p>
                        </div>
                        <div class="stat-card">
                            <h3>Favorite Game</h3>
                            <p class="stat-value" id="favoriteGame">...</p>
                        </div>
                        <div class="stat-card">
                            <h3>Biggest Win</h3>
                            <p class="stat-value big-win" id="biggestWin">...</p>
                        </div>
                    </div>

                    <div class="chart-area">
                        <h3>Wagered Over Time (Last 7 Days)</h3>
                        <canvas id="wageredOverTimeChart"></canvas>
                    </div>

                    <div class="chart-area">
                        <h3>Bets by Game Type</h3>
                        <canvas id="betsByGameTypeChart"></canvas>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div id="topUpModal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="closeTopUpModal">×</span>
            <h2>Deposit Funds</h2>
            <div class="form-group">
                <label for="modalDepositAmount">Amount to Deposit ($)</label>
                <input type="number" id="modalDepositAmount" value="50.00" min="0.01" step="0.01">
            </div>
            <button id="confirmDepositBtn">Confirm Deposit</button>
            <p id="modalMessage" class="modal-message"></p>
        </div>
    </div>

    <script>
        const API_BASE_URL = 'http://localhost/sugalhero-api'; // Adjust if your API folder name is different
        let currentUserToken = null; // Will store the JWT

        // New Element IDs for balance display
        //const fiatBalanceDisplay = document.getElementById('fiatBalanceDisplay');
        const sgcBalanceDisplay = document.getElementById('sgcBalanceDisplay');


        // --- Page Protection & Initial Data Load ---
        document.addEventListener('DOMContentLoaded', async () => {
            const jwtToken = localStorage.getItem('jwt_token');
            const username = localStorage.getItem('username');
            const userId = localStorage.getItem('user_id');

            if (!jwtToken || !username || !userId) {
                window.location.href = '../authentication/login.php';
                return;
            }

            currentUserToken = jwtToken; // Store token for global use in this script

            document.getElementById('headerUsername').textContent = username;
            document.getElementById('welcomeUsername').textContent = username;

            document.getElementById('userDropdownToggle').addEventListener('click', () => {
                document.getElementById('userDropdownMenu').classList.toggle('show');
            });

            window.onclick = function(event) {
                if (!event.target.matches('.dropdown-toggle') && !event.target.closest('.dropdown-toggle')) {
                    const dropdown = document.getElementById("userDropdownMenu");
                    if (dropdown && dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show');
                    }
                }
            };

            // Event listener for Logout button
            document.getElementById('logoutBtn').addEventListener('click', () => {
                localStorage.removeItem('jwt_token');
                localStorage.removeItem('user_id');
                localStorage.removeItem('username');
                window.location.href = '../authentication/login.php';
            });

            // --- Event listener for Top Up button to OPEN MODAL ---
            document.getElementById('topUpBtn').addEventListener('click', () => {
                document.getElementById('topUpModal').style.display = 'flex'; // Show modal
                document.getElementById('modalDepositAmount').value = '50.00'; // Reset amount
                document.getElementById('modalMessage').textContent = ''; // Clear previous messages
            });

            // --- Event listener for Close Modal button ---
            document.getElementById('closeTopUpModal').addEventListener('click', () => {
                document.getElementById('topUpModal').style.display = 'none'; // Hide modal
            });

            // Hide modal if user clicks outside of modal content
            window.addEventListener('click', (event) => {
                if (event.target == document.getElementById('topUpModal')) {
                    document.getElementById('topUpModal').style.display = 'none';
                }
            });

            // Fetch initial data (balance, transactions, and statistics)
            await fetchAndDisplayBalance();
            await fetchAndDisplayTransactions();
            await fetchAndDisplayStatistics();
        });

        // --- API Calls and Display Functions ---

        async function fetchAndDisplayBalance() {
            // Updated to fetch and display both Fiat and SGC balances
            const headerBalanceDisplay = document.getElementById('headerBalanceDisplay'); // For the dropdown
            if (!currentUserToken) {
                console.error('No JWT token found. Cannot fetch balance.');
                return;
            }

            try {
                // Using user_profile.php (as discussed in previous turn) which should return both balances
                const response = await fetch(`${API_BASE_URL}/user_profile.php`, { // IMPORTANT: Ensure this API endpoint exists and returns both balances!
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${currentUserToken}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    console.error('API Error fetching balance:', response.status, errorData.message || 'Unknown error');
                    // Handle token expiry/invalidity
                    if (response.status === 401 || (errorData.message && (errorData.message.includes('Unauthorized') || errorData.message.includes('token')))) {
                        alert('Your session has expired or is invalid. Please log in again.');
                        localStorage.removeItem('jwt_token');
                        localStorage.removeItem('user_id');
                        localStorage.removeItem('username');
                        window.location.href = '../authentication/login.php';
                    }
                    //fiatBalanceDisplay.textContent = 'Error!';
                    sgcBalanceDisplay.textContent = 'Error!';
                    headerBalanceDisplay.textContent = 'Balance: Error!';
                    return;
                }

                const data = await response.json();
                if (data.success && data.user) {
                    // Update header dropdown balance (you might choose to show only fiat or a combined value here)
                    headerBalanceDisplay.textContent = `Balance: ₱${parseFloat(data.user.fiat_balance).toFixed(2)}`;

                    // Update dedicated balance cards
                    //fiatBalanceDisplay.textContent = `₱ ${parseFloat(data.user.fiat_balance).toFixed(2)}`;
                    // For SGC, ensure it displays with the correct number of decimals (18, as per our prior discussion)
                    sgcBalanceDisplay.textContent = `${parseFloat(data.user.token_balance).toFixed(18)} SGC`;
                } else {
                    console.error('API Error fetching balance (success:false or no user data):', data.message);
                    fiatBalanceDisplay.textContent = 'N/A';
                    sgcBalanceDisplay.textContent = 'N/A';
                    headerBalanceDisplay.textContent = 'Balance: N/A';
                }
            } catch (error) {
                console.error('Network or Fetch Error fetching balance:', error);
                //fiatBalanceDisplay.textContent = 'Network Error!';
                sgcBalanceDisplay.textContent = 'Network Error!';
                headerBalanceDisplay.textContent = 'Balance: Error!';
            }
        }

        async function fetchAndDisplayTransactions() {
            const transactionsTableBody = document.getElementById('transactionsTableBody');
            if (!transactionsTableBody || !currentUserToken) return;

            transactionsTableBody.innerHTML = '<tr><td colspan="5">Loading transactions...</td></tr>';

            try {
                // IMPORTANT: This endpoint (`user_transactions.php`) must return transactions
                // that include `currency_type`, `type` (e.g., 'deposit', 'bet', 'win', 'withdrawal'),
                // `amount`, `game_name` (if applicable), and `created_at` (or similar timestamp).
                const response = await fetch(`${API_BASE_URL}/user_transactions.php?limit=20`, { // Increased limit for better view
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${currentUserToken}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    console.error('API Error fetching transactions:', response.status, errorData.message || 'Unknown error');
                    if (response.status === 401 || (errorData.message && (errorData.message.includes('Unauthorized') || errorData.message.includes('token')))) {
                        alert('Your session has expired or is invalid. Please log in again.');
                        localStorage.removeItem('jwt_token');
                        localStorage.removeItem('user_id');
                        localStorage.removeItem('username');
                        window.location.href = '../authentication/login.php';
                    }
                    transactionsTableBody.innerHTML = '<tr><td colspan="5" style="color: red;">Error loading transactions.</td></tr>';
                    return;
                }

                const data = await response.json();

                if (data.success && data.transactions && data.transactions.length > 0) {
                    transactionsTableBody.innerHTML = ''; // Clear loading message

                    data.transactions.forEach(transaction => {
                        const row = transactionsTableBody.insertRow();
                        const gameCell = row.insertCell();
                        const typeCell = row.insertCell();
                        const amountCell = row.insertCell();
                        const outcomeCell = row.insertCell();
                        const dateCell = row.insertCell();

                        const isSgcTransaction = transaction.currency_type === 'token';
                        const displayAmount = isSgcTransaction ?
                            `${parseFloat(transaction.amount).toFixed(18)} SGC` : // SGC with 18 decimals
                            `₱${parseFloat(transaction.amount).toFixed(2)}`;   // Fiat with 2 decimals

                        let transactionTypeLabel = transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1);
                        let outcomeDescription = '';
                        let amountClass = ''; // For styling positive/negative amounts

                        // Logic for Outcome/Description and amount styling
                        if (transaction.type === 'deposit') {
                            outcomeDescription = isSgcTransaction ? 'Bought SugalCoin' : 'Fiat Deposit';
                            amountClass = 'positive';
                        } else if (transaction.type === 'withdrawal') {
                            outcomeDescription = isSgcTransaction ? 'SugalCoin Withdrawal' : 'Fiat Withdrawal';
                            amountClass = 'negative';
                        } else if (transaction.type === 'bet') {
                            outcomeDescription = `Bet on ${transaction.game_name || 'Game'}`;
                            amountClass = 'negative';
                            // If it's a 'bet' type, and the amount is positive, it means a win.
                            // The backend should ideally differentiate between a bet loss and a win.
                            // For simplicity, here we'll assume a positive `amount` for a 'bet' type is actually a payout.
                            // Better: Backend sends 'type: 'bet_loss'' and 'type: 'bet_win''
                            if (parseFloat(transaction.amount) > 0) { // If a 'bet' type somehow has a positive amount, means win
                                outcomeDescription = `Won on ${transaction.game_name || 'Game'}`;
                                amountClass = 'positive';
                            }
                        } else if (transaction.type === 'win') { // Explicit 'win' type from backend game history
                            outcomeDescription = `Won on ${transaction.game_name || 'Game'}`;
                            amountClass = 'positive';
                        } else if (transaction.type === 'loss') { // Explicit 'loss' type from backend game history
                            outcomeDescription = `Lost on ${transaction.game_name || 'Game'}`;
                            amountClass = 'negative';
                        } else if (transaction.type === 'transfer') {
                            outcomeDescription = 'SugalCoin Transfer';
                            amountClass = (parseFloat(transaction.amount) >= 0) ? 'positive' : 'negative'; // Could be sending or receiving
                        } else {
                            outcomeDescription = transaction.description || 'N/A';
                        }

                        // Apply amount class for styling (green for positive, red for negative)
                        if (parseFloat(transaction.amount) > 0 && amountClass !== 'positive') {
                            amountClass = 'positive'; // Default to positive if not set
                        } else if (parseFloat(transaction.amount) < 0 && amountClass !== 'negative') {
                            amountClass = 'negative'; // Default to negative if not set
                        }


                        gameCell.textContent = transaction.game_name || 'N/A';
                        typeCell.textContent = transactionTypeLabel;
                        amountCell.innerHTML = `<span class="${amountClass}">${displayAmount}</span>`;
                        outcomeCell.textContent = outcomeDescription;
                        dateCell.textContent = new Date(transaction.created_at).toLocaleString(); // Assumes 'created_at' for all transactions

                        // Add a class to the row for specific styling if needed (e.g., 'sgc-row', 'fiat-row')
                        if (isSgcTransaction) {
                            row.classList.add('sgc-transaction');
                        } else {
                            row.classList.add('fiat-transaction');
                        }
                    });
                } else {
                    transactionsTableBody.innerHTML = '<tr><td colspan="5">No transactions found.</td></tr>';
                }
            } catch (error) {
                console.error('Network or API Error fetching transactions:', error);
                transactionsTableBody.innerHTML = '<tr><td colspan="5" style="color: red;">Error loading transactions.</td></tr>';
            }
        }

        async function fetchAndDisplayStatistics() {
            if (!currentUserToken) return;

            try {
                const response = await fetch(`${API_BASE_URL}/user_statistics.php`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${currentUserToken}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    console.error('API Error fetching statistics:', response.status, errorData.message || 'Unknown error');
                    localStorage.removeItem('jwt_token');
                    localStorage.removeItem('user_id');
                    localStorage.removeItem('username');
                    window.location.href = '../authentication/login.php';
                    return;
                }

                const data = await response.json();

                if (data.success && data.statistics) {
                    const stats = data.statistics;

                    document.getElementById('totalBets').textContent = stats.total_bets_placed.toLocaleString();
                    document.getElementById('totalWagered').textContent = `$${stats.total_wagered.toFixed(2)}`;

                    const netProfitLossElement = document.getElementById('netProfitLoss');
                    netProfitLossElement.textContent = `$${stats.net_profit_loss.toFixed(2)}`;
                    if (stats.net_profit_loss > 0) {
                        netProfitLossElement.classList.add('profit');
                        netProfitLossElement.classList.remove('loss');
                    } else if (stats.net_profit_loss < 0) {
                        netProfitLossElement.classList.add('loss');
                        netProfitLossElement.classList.remove('profit');
                    } else {
                        netProfitLossElement.classList.remove('profit', 'loss');
                    }

                    document.getElementById('winRate').textContent = `${stats.win_rate.toFixed(1)}%`;
                    document.getElementById('favoriteGame').textContent = stats.favorite_game;
                    document.getElementById('biggestWin').textContent = `$${stats.biggest_win.toFixed(2)}`;

                    const wageredOverTimeCtx = document.getElementById('wageredOverTimeChart').getContext('2d');
                    new Chart(wageredOverTimeCtx, {
                        type: 'line',
                        data: {
                            labels: stats.wagered_over_time_labels,
                            datasets: [{
                                label: 'Daily Wagered ($)',
                                data: stats.wagered_over_time_data,
                                borderColor: 'rgb(75, 192, 192)',
                                tension: 0.1,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    labels: {
                                        color: '#E0E0E0'
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)'
                                    },
                                    ticks: {
                                        color: '#E0E0E0'
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)'
                                    },
                                    ticks: {
                                        color: '#E0E0E0'
                                    }
                                }
                            }
                        }
                    });

                    const betsByGameTypeCtx = document.getElementById('betsByGameTypeChart').getContext('2d');
                    new Chart(betsByGameTypeCtx, {
                        type: 'bar',
                        data: {
                            labels: stats.bets_by_game_type_labels,
                            datasets: [{
                                label: 'Number of Bets',
                                data: stats.bets_by_game_type_data,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.6)', 'rgba(54, 162, 235, 0.6)', 'rgba(255, 206, 86, 0.6)',
                                    'rgba(75, 192, 192, 0.6)', 'rgba(153, 102, 255, 0.6)', 'rgba(255, 159, 64, 0.6)'
                                ],
                                borderColor: [
                                    'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false,
                                    labels: {
                                        color: '#E0E0E0'
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)'
                                    },
                                    ticks: {
                                        color: '#E0E0E0'
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)'
                                    },
                                    ticks: {
                                        color: '#E0E0E0'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    console.log('No statistics data found or API reported success:false.', data);
                }
            } catch (error) {
                console.error('Network or API Error fetching statistics (catch block):', error);
            }
        }

        // --- Function to handle deposit from modal ---
        async function handleDeposit() {
            const amountInput = document.getElementById('modalDepositAmount');
            const amount = parseFloat(amountInput.value);
            const modalMessage = document.getElementById('modalMessage');

            if (isNaN(amount) || amount <= 0) {
                modalMessage.textContent = "Please enter a valid positive amount.";
                modalMessage.className = 'modal-message error';
                return;
            }

            modalMessage.textContent = "Processing deposit...";
            modalMessage.className = 'modal-message info';

            try {
                const response = await fetch(`${API_BASE_URL}/deposit.php`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${currentUserToken}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ amount: amount })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    modalMessage.textContent = "Deposit successful!";
                    modalMessage.className = 'modal-message success';
                    amountInput.value = '50.00'; // Reset input
                    await fetchAndDisplayBalance();
                    await fetchAndDisplayTransactions();
                } else {
                    modalMessage.textContent = data.message || "Deposit failed. Please try again.";
                    modalMessage.className = 'modal-message error';
                    if (response.status === 401) {
                        localStorage.removeItem('jwt_token');
                        localStorage.removeItem('user_id');
                        localStorage.removeItem('username');
                        window.location.href = '../authentication/login.php';
                    }
                }
            } catch (error) {
                modalMessage.textContent = "Network error. Please check your connection.";
                modalMessage.className = 'modal-message error';
                console.error('Deposit network error:', error);
            }
        }

        // Event listener for Confirm Deposit Button in modal
        document.getElementById('confirmDepositBtn').addEventListener('click', handleDeposit);

        // --- Nav item click handlers ---
        document.getElementById('dashboardLink').addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
            e.currentTarget.classList.add('active');
        });

        document.getElementById('statisticsLink').addEventListener('click', (e) => {
            e.preventDefault();
            const statisticsSection = document.querySelector('.statistics-window');
            if (statisticsSection) {
                statisticsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
            e.currentTarget.classList.add('active');
        });

        document.getElementById('transactionsLink').addEventListener('click', (e) => {
            e.preventDefault();
            const transactionsSection = document.querySelector('.betting-history-window');
            if (transactionsSection) {
                transactionsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
            e.currentTarget.classList.add('active');
        });

        document.getElementById('settingsLink').addEventListener('click', (e) => {
            // No need to preventDefault since href is already set to user_settings.php
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
            e.currentTarget.classList.add('active');
            // Navigation to user_settings.php is handled by the href
        });

        document.getElementById('helpLink').addEventListener('click', (e) => {
            // No need to preventDefault since href is already set to user_help.php
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
            e.currentTarget.classList.add('active');
            // Navigation to user_help.php is handled by the href
        });
    </script>
</body>
</html>