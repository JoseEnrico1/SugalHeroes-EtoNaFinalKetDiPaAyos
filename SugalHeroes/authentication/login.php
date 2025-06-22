<?php
// authentication/login.php

// You had session_start() here. For API-driven login, sessions are generally
// not used for authentication state. You might keep it if other parts of your
// website still rely on PHP sessions for non-API functionality,
// but for the login process itself, we're shifting to JWT.
// session_start(); // Comment out if you no longer need PHP sessions for login state

// Removed old PHP database connection, login processing, and session management.
// These are now handled by your sugalhero-api/login.php endpoint.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | SUGALHEROES</title>
  <link rel="stylesheet" href="authstyle.css" />
</head>
<body>
  <div class="auth-container">
    <h1>Login</h1> <form id="loginForm">
      <input type="text" id="username" name="username" placeholder="Username" required />
      <input type="password" id="password" name="password" placeholder="Password" required />
      <button type="submit">Login</button>
      <p id="message" class="login-message"></p> <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
    </form>
  </div>

  <script>
    // Adjust this URL to point to your API's login endpoint
    const API_LOGIN_URL = 'http://localhost/sugalhero-api/login.php';

    const loginForm = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const messageDisplay = document.getElementById('message'); // The element to show messages

    // --- Check for existing JWT token on page load ---
    // If a token exists, redirect the user immediately as they are "logged in"
    document.addEventListener('DOMContentLoaded', () => {
        if (localStorage.getItem('jwt_token')) {
            console.log('User already logged in via JWT. Redirecting...');
            // Adjust this path based on where your index.php is relative to login.php
            // e.g., '../homepage/index.php' assumes authentication/ is sibling to homepage/
            window.location.href = '../homepage/index.php';
        }
    });

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault(); // Prevent default form submission (stops page reload)

        const username = usernameInput.value;
        const password = passwordInput.value;

        messageDisplay.textContent = 'Logging in...';
        messageDisplay.style.color = '#007bff'; // Example loading color

        try {
            const response = await fetch(API_LOGIN_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json(); // Parse the JSON response from the API

            if (response.ok) { // Check for 2xx status code (e.g., 200 OK)
                messageDisplay.textContent = data.message;
                messageDisplay.style.color = 'green'; // Success message color

                // Store the JWT token and user info securely in localStorage
                localStorage.setItem('jwt_token', data.token);
                localStorage.setItem('user_id', data.userId);
                localStorage.setItem('username', data.username);

                console.log('Login successful:', data);
                console.log('Token stored:', data.token);

                // Redirect to your homepage
                window.location.href = '../homepage/index.php'; // Adjust this path if needed
            } else {
                // Login failed (API returned an error status like 401, 400, 500)
                messageDisplay.textContent = data.message || 'Login failed. Please try again.';
                messageDisplay.style.color = 'red'; // Error message color
                console.error('Login failed:', data);
            }

        } catch (error) {
            // Network error or other fetch-related issues
            messageDisplay.textContent = 'An error occurred. Please check your connection.';
            messageDisplay.style.color = 'red';
            console.error('Network error during login:', error);
        }
    });
  </script>
</body>
</html>