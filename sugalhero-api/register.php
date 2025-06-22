<?php
// Include necessary files
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader
require_once __DIR__ . '/config.php';          // Configuration (DB credentials, JWT secret)
require_once __DIR__ . '/database.php';        // Database connection function

// Set content type to JSON and handle CORS (defined in config.php)
// header('Content-Type: application/json'); // Already handled in config.php

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

// Get raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['username'], $data['email'], $data['password']) ||
    empty($data['username']) || empty($data['email']) || empty($data['password'])) {
    sendJsonResponse(['success' => false, 'message' => 'Username, email, and password are required.'], 400);
}

$username = $data['username'];
$email = $data['email'];
$password = $data['password'];

// Basic email format validation (can be more robust)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid email format.'], 400);
}

// Password hashing
// Use password_hash for secure password storage. PASSWORD_BCRYPT is recommended.
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Connect to database
$conn = getDbConnection();

// Check if username or email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
if ($stmt === false) {
    handleDbError($conn); // Use helper function for database errors
}
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    sendJsonResponse(['success' => false, 'message' => 'Username or email already exists.'], 409); // 409 Conflict
}
$stmt->close();

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (username, email, password, currency_balance) VALUES (?, ?, ?, ?)");
if ($stmt === false) {
    handleDbError($conn);
}

$initialBalance = 0.00; // New users start with 0 balance
$stmt->bind_param("sssd", $username, $email, $hashedPassword, $initialBalance); // 'd' for double/decimal

if ($stmt->execute()) {
    $userId = $conn->insert_id; // Get the ID of the newly inserted user
    sendJsonResponse(['success' => true, 'message' => 'User registered successfully!', 'userId' => $userId], 201); // 201 Created
} else {
    handleDbError($conn);
}

$stmt->close();
$conn->close();
?>