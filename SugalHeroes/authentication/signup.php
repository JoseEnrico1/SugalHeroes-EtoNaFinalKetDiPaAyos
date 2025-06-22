<?php
// authentication/signup.php

// Removed old PHP database connection, signup processing, and session management.
// These are now handled by your sugalhero-api/register.php endpoint.
// Removed session_start() and PHP session check for existing login (handled by JS/JWT).
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Signup | SUGALHEROES</title>
  <link rel="stylesheet" href="authstyle.css" />
</head>
<body>
  <div class="auth-container">
    <h1>Sign Up</h1> <form id="signupForm">
      <input type="text" id="username" name="username" placeholder="Username" required />
      <input type="email" id="email" name="email" placeholder="Email" required />
      <input type="password" id="password" name="password" placeholder="Password" required />
      <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required />
      <button type="submit">Sign Up</button>
      <p id="message" class="signup-message"></p> <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
  </div>

  <script>
    // Adjust this URL to point to your API's registration endpoint
    // (It's register.php, but if you renamed it to signup.php on the API side, change this)
    const API_REGISTER_URL = 'http://localhost/sugalhero-api/register.php';

    const signupForm = document.getElementById('signupForm');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const messageDisplay = document.getElementById('message'); // The element to show messages

    // --- Check for existing JWT token on page load ---
    // If a token exists, redirect the user immediately as they are "logged in"
    document.addEventListener('DOMContentLoaded', () => {
        if (localStorage.getItem('jwt_token')) {
            console.log('User already logged in via JWT. Redirecting...');
            // Adjust this path based on where your index.php is relative to signup.php
            window.location.href = '../homepage/index.php'; // Example path
        }
    });

    signupForm.addEventListener('submit', async (event) => {
        event.preventDefault(); // Prevent default form submission (stops page reload)

        const username = usernameInput.value;
        const email = emailInput.value;
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Frontend validation (basic)
        if (!username || !email || !password || !confirmPassword) {
            messageDisplay.textContent = 'All fields are required.';
            messageDisplay.style.color = 'red';
            return;
        }
        if (password !== confirmPassword) {
            messageDisplay.textContent = 'Passwords do not match.';
            messageDisplay.style.color = 'red';
            return;
        }
        // You might add more frontend password strength validation here

        messageDisplay.textContent = 'Signing up...';
        messageDisplay.style.color = '#007bff'; // Example loading color

        try {
            const response = await fetch(API_REGISTER_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                // Send only username, email, and password (API doesn't need confirm_password)
                body: JSON.stringify({ username, email, password })
            });

            const data = await response.json(); // Parse the JSON response from the API

            if (response.ok) { // Check for 2xx status code (e.g., 201 Created)
                messageDisplay.textContent = data.message + ' You can now log in.';
                messageDisplay.style.color = 'green'; // Success message color

                console.log('Signup successful:', data);

                // Optional: Clear form fields after successful signup
                usernameInput.value = '';
                emailInput.value = '';
                passwordInput.value = '';
                confirmPasswordInput.value = '';

                // You might want to redirect to login page after successful signup
                // window.location.href = 'login.php';

            } else {
                // Signup failed (API returned an error status like 400, 409, 500)
                messageDisplay.textContent = data.message || 'Signup failed. Please try again.';
                messageDisplay.style.color = 'red'; // Error message color
                console.error('Signup failed:', data);
            }

        } catch (error) {
            // Network error or other fetch-related issues
            messageDisplay.textContent = 'An error occurred. Please check your connection.';
            messageDisplay.style.color = 'red';
            console.error('Network error during signup:', error);
        }
    });
  </script>
</body>
</html>