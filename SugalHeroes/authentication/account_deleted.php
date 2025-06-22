<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deleted - SUGALHEROES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- EMBEDDED CSS FOR THIS PAGE ONLY --- */

        /* Color Palette & Font Imports (from dashboard.css :root, directly embedded) */
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Orbitron:wght@400;700&display=swap');

        :root {
            --bg-dark: #1a1a1a;
            --bg-medium: #222222;
            --text-color: #e0e0e0;
            --accent-green: #76b900;
            --loss-color: #d12222; /* Red for 'deleted' status */
            --font-heading: 'Orbitron', sans-serif;
            --font-body: 'Roboto', sans-serif;
        }

        /* General Body Styling */
        body {
            background-color: var(--bg-dark);
            color: var(--text-color);
            font-family: var(--font-body); /* Roboto for body */
            display: flex;
            flex-direction: column; /* Stack elements vertically */
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            overflow: hidden;
            padding: 20px;
            box-sizing: border-box;
        }

        /* Header Section: Logo + Brand Name */
        .header-section {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            color: var(--text-color);
        }

        .header-section img {
            max-width: 100px; /* Size for the logo */
            height: auto;
        }

        .header-section h1 {
            font-family: var(--font-heading); /* Orbitron font */
            font-size: 3.5em;
            color: var(--accent-green); /* NVIDIA green */
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Message Text */
        .message-text {
            font-family: var(--font-body);
            font-size: 1.4em;
            color: var(--text-color);
            text-align: center;
            margin-bottom: 30px;
            max-width: 600px;
        }

        /* Sign Up Again Button */
        .signup-redirect-btn {
            background-color: var(--accent-green);
            color: var(--bg-dark); /* Dark text on green */
            padding: 15px 35px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-family: var(--font-body);
            font-size: 1.2em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .signup-redirect-btn:hover {
            background-color: #609d00;
            transform: translateY(-2px);
        }
        .signup-redirect-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="header-section">
        <img src="../images/logo.png" alt="Your Brand Logo"> <h1>SUGALHEROES</h1>
    </div>

    <p class="message-text">Your account has been successfully removed from our system. Enjoy your gambling free days!... or continue spending your savings on us? 😈</p>
    
    <button id="signupRedirectBtn" class="signup-redirect-btn">Sign Up Again?</button>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('signupRedirectBtn').addEventListener('click', () => {
                window.location.href = 'signup.php'; // Redirect to signup page
            });
            // Clear any lingering JWT just in case
            localStorage.removeItem('jwt_token');
            localStorage.removeItem('user_id');
            localStorage.removeItem('username');
        });
    </script>
</body>
</html>