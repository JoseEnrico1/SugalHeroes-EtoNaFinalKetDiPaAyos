<?php
// authentication/logout.php

// Removed old PHP session management.
// For JWT-based authentication, logout happens client-side by clearing the token.
// Any PHP here would only be for server-side processing if needed (e.g., logging logout events),
// but not for managing the user's logged-in state.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .message-container {
            text-align: center;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="message-container">
        <h2>Logging you out...</h2>
        <p>Please wait while we log you out.</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Clear the JWT token and user info from local storage
            localStorage.removeItem('jwt_token');
            localStorage.removeItem('user_id');
            localStorage.removeItem('username');

            console.log('JWT token and user info cleared from local storage.');

            // Redirect to the login page
            // Adjust this path based on where your login.php is relative to logout.php
            // e.g., 'login.php' assumes they are in the same folder
            // e.g., '../authentication/login.php' if they are sibling to the folder containing logout.php
            window.location.href = 'login.php'; // This is generally the most common redirect after logout

            // If you prefer to redirect to the homepage (index.php) instead, use this:
            // window.location.href = '../homepage/index.php';
        });
    </script>
</body>
</html>