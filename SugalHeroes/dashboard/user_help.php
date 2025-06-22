<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - SUGALHEROES</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Specific styles for help page elements not fully covered by dashboard.css */
        .help-section-card {
            background-color: var(--bg-medium);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .help-section-card h2 {
            font-family: var(--font-heading);
            color: var(--accent-green);
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            font-size: 1.5em;
            display: flex;
            align-items: center;
        }
        .help-section-card h2 i {
            margin-right: 10px;
            font-size: 1.2em;
            color: var(--accent-green);
        }
        .help-content {
            font-family: var(--font-body);
            color: var(--text-color);
            line-height: 1.6;
        }
        .help-content h3 {
            font-family: var(--font-heading);
            color: var(--text-color);
            font-size: 1.2em;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .help-content ul {
            list-style: none;
            padding-left: 20px;
        }
        .help-content ul li {
            margin-bottom: 8px;
        }
        .help-content a {
            color: var(--accent-blue);
            text-decoration: none;
        }
        .help-content a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/newlogo.png" alt="Your Brand Logo" id="sidebarLogo">
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../homepage/index.php" class="nav-item"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="../homepage/index.php#games-section" class="nav-item"><i class="fas fa-gamepad"></i> Games</a></li>
                    <li><a href="user_dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    <li><a href="user_settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="user_help.php" class="nav-item active"><i class="fas fa-question-circle"></i> Help</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content" id="topOfHelp">
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
                <h1><i class="fas fa-question-circle"></i> Help & Support</h1>
                <p>Find answers to common questions or contact our support team.</p>

                <div class="help-section-card">
                    <h2><i class="fas fa-question"></i> Frequently Asked Questions</h2>
                    <div class="help-content">
                        <h3>How do I deposit funds?</h3>
                        <p>You can deposit funds by clicking the "Top Up" button in your account dropdown on the homepage or dashboard. Follow the instructions in the modal that appears.</p>

                        <h3>How are winnings calculated in Plinko?</h3>
                        <p>In Plinko, your bet is multiplied by the multiplier in the slot where your ball lands. Your payout is your original bet times that multiplier.</p>

                        <h3>What are the rules for Kara Krus?</h3>
                        <p>Kara Krus is a coin flip game against the dealer. If both coins are 'Krus', you win double your bet. If both are 'Kara', you lose your bet. If it's one 'Kara' and one 'Krus', it's a draw and your bet is returned.</p>

                        <h3>How do I change my password?</h3>
                        <p>You can change your password by navigating to your <a href="user_settings.php">Account Settings</a> page.</p>

                        <h3>Is my account secure?</h3>
                        <p>Yes, we use secure hashing for passwords and JSON Web Tokens (JWTs) for authentication to keep your account safe.</p>
                    </div>
                </div>

                <div class="help-section-card">
                    <h2><i class="fas fa-headset"></i> Contact Support</h2>
                    <div class="help-content">
                        <p>If you cannot find an answer to your question, please reach out to our support team:</p>
                        <ul>
                            <li>Email: <a href="mailto:support@sugalheroes.com">support@sugalheroes.com</a></li>
                            <li>Live Chat: Available daily from 9 AM - 5 PM PST (coming soon)</li>
                        </ul>
                        <h3>Feedback & Suggestions</h3>
                        <p>We'd love to hear your thoughts! Send us your feedback at <a href="mailto:feedback@sugalheroes.com">feedback@sugalheroes.com</a>.</p>
                    </div>
                </div>

                <div class="help-section-card">
                    <h2><i class="fas fa-info-circle"></i> Terms & Privacy</h2>
                    <div class="help-content">
                        <p>For detailed information about our terms of service and privacy policy, please refer to the links below:</p>
                        <ul>
                            <li><a href="#">Terms of Service (coming soon)</a></li>
                            <li><a href="#">Privacy Policy (coming soon)</a></li>
                        </ul>
                    </div>
                </div>

            </section>
        </main>
    </div>

    <div id="topUpModal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="closeTopUpModal">&times;</span>
            <h2>Deposit Funds</h2>
            <div class="form-group">
                <label for="modalDepositAmount">Amount to Deposit ($)</label>
                <input type="number" id="modalDepositAmount" value="50.00" min="0.01" step="0.01">
            </div>
            <button id="confirmDepositBtn">Confirm Deposit</button>
            <p id="modalMessage" class="modal-message"></p>
        </div>
    </div>

    <script src="user_help.js"></script>
</body>
</html>