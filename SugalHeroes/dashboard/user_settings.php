<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings - SUGALHEROES</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Specific styles for settings page elements not fully covered by dashboard.css */
        /* These styles ensure your forms and info cards look consistent with the dashboard's aesthetic */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .settings-form-card {
            background-color: var(--bg-medium);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .settings-form-card h2 {
            font-family: var(--font-heading);
            color: var(--accent-green);
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            font-size: 1.5em;
            display: flex; /* For icon alignment */
            align-items: center;
        }
        .settings-form-card h2 i {
            margin-right: 10px;
            font-size: 1.2em;
            color: var(--accent-green);
        }
        .form-group-settings {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group-settings label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--text-color);
        }
        .form-group-settings label i {
            margin-right: 8px;
            color: var(--accent-green);
        }
        .form-group-settings input[type="text"],
        .form-group-settings input[type="password"],
        .form-group-settings input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: var(--bg-light);
            color: var(--text-color);
            font-size: 1em;
        }
        /* Style for buttons within settings forms */
        .form-group-settings button,
        .settings-form-card button {
            background: linear-gradient(to right, var(--button-gradient-start), var(--button-gradient-end));
            color: var(--text-color);
            padding: 10px 20px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.1s ease;
            width: auto;
            align-self: flex-start; /* Align button to left within its flex container */
            margin-top: 10px;
        }
        .form-group-settings button:hover,
        .settings-form-card button:hover {
            background-color: var(--hover-bg);
            border-color: var(--accent-green);
            transform: translateY(-2px);
        }
        .form-group-settings button:active,
        .settings-form-card button:active {
            transform: translateY(0);
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.5);
        }
        /* Specific color for delete button */
        .settings-form-card button.delete-button {
            background-color: var(--loss-color);
            border-color: var(--loss-color);
        }
        .settings-form-card button.delete-button:hover {
            background-color: #c82333;
            border-color: #c82333;
        }

        .settings-message {
            margin-top: 10px;
            font-weight: bold;
            text-align: center;
        }
        .settings-message.success { color: var(--win-color); }
        .settings-message.error { color: var(--loss-color); }
        .settings-message.info { color: var(--accent-blue); }

        /* General dashboard links from dashboard.css (Ensure consistency with homepage header) */
        .home-button {
            background: linear-gradient(to right, var(--button-gradient-start), var(--button-gradient-end));
            color: var(--text-color);
            padding: 10px 20px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 1em;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .home-button i { margin-right: 8px; color: var(--accent-green); }
        .home-button:hover { background-color: var(--hover-bg); border-color: var(--accent-green); }
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
                    <li><a href="user_settings.php" class="nav-item active"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="user_help.php" class="nav-item"><i class="fas fa-question-circle"></i> Help</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content" id="topOfSettings"> <header class="header">
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
                <h1><i class="fas fa-cog"></i> Account Settings</h1>
                <p>Manage your profile, security, and preferences here.</p>

                <div class="settings-grid">
                    <div class="settings-form-card">
                        <h2><i class="fas fa-user"></i> Your Profile</h2>
                        <div class="form-group-settings">
                            <label>Username:</label>
                            <p id="currentUsername" style="font-weight: bold; color: var(--accent-green);"></p>
                        </div>
                        <div class="form-group-settings">
                            <label>Email:</label>
                            <p id="currentUserEmail"></p>
                        </div>
                        <div class="form-group-settings">
                            <label>Current Balance:</label>
                            <p id="currentBalanceDisplay" style="font-weight: bold; color: var(--accent-green);"></p>
                        </div>
                    </div>

                    <div class="settings-form-card">
                        <h2><i class="fas fa-key"></i> Change Password</h2>
                        <div class="form-group-settings">
                            <label for="oldPassword"><i class="fas fa-lock"></i> Old Password:</label>
                            <input type="password" id="oldPassword" required>
                        </div>
                        <div class="form-group-settings">
                            <label for="newPassword"><i class="fas fa-key"></i> New Password:</label>
                            <input type="password" id="newPassword" required>
                        </div>
                        <div class="form-group-settings">
                            <label for="confirmNewPassword"><i class="fas fa-key"></i> Confirm New Password:</label>
                            <input type="password" id="confirmNewPassword" required>
                        </div>
                        <button id="changePasswordBtn">Change Password</button>
                        <p id="passwordMessage" class="settings-message"></p>
                    </form>
                </div>

                <div class="settings-form-card">
                    <h2><i class="fas fa-user-edit"></i> Change Username</h2>
                    <div class="form-group-settings">
                        <label for="newUsername"><i class="fas fa-user-edit"></i> New Username:</label>
                        <input type="text" id="newUsername" required>
                    </div>
                    <button id="changeUsernameBtn">Change Username</button>
                    <p id="usernameMessage" class="settings-message"></p>
                </div>

                <div class="settings-form-card">
                    <h2><i class="fas fa-bell"></i> Notifications</h2>
                    <p>Notification settings coming soon!</p>
                </div>
                <div class="settings-form-card">
                    <h2><i class="fas fa-user-times"></i> Account Deletion</h2>
                    <p>Carefully manage your account deletion preferences here.</p>
                    <button id="deleteAccountBtn" class="delete-button"><i class="fas fa-trash-alt"></i> Delete Account</button>
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

    <script src="user_settings.js"></script>
</body>
</html>