<?php
// sugalhero-api/change_username.php

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader
require_once __DIR__ . '/config.php';          // Configuration
require_once __DIR__ . '/database.php';        // Database connection
require_once __DIR__ . '/auth_helper.php';     // JWT functions

// Set content type to JSON and handle CORS

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected POST.'], 405);
}

// Validate JWT token and get user ID
$userId = validateJwtToken(); // This function will exit if token is invalid/missing

// Get raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// --- Input Validation for New Username ---
if (!isset($data['new_username']) || empty($data['new_username'])) {
    sendJsonResponse(['success' => false, 'message' => 'New username is required.'], 400);
}

$newUsername = trim($data['new_username']); // Trim whitespace
// Basic validation: username length, allowed characters (you can make this stricter)
if (strlen($newUsername) < 3 || strlen($newUsername) > 50) {
    sendJsonResponse(['success' => false, 'message' => 'Username must be between 3 and 50 characters.'], 400);
}
if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $newUsername)) { // Allow letters, numbers, underscore, dot, hyphen
    sendJsonResponse(['success' => false, 'message' => 'Username contains invalid characters.'], 400);
}


// Connect to database
$conn = getDbConnection();

// Start a database transaction for atomicity
$conn->begin_transaction();

try {
    // 1. Check if the new username already exists for another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("si", $newUsername, $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $conn->rollback();
        sendJsonResponse(['success' => false, 'message' => 'This username is already taken.'], 409); // 409 Conflict
    }
    $stmt->close();

    // 2. Update the username
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("si", $newUsername, $userId);
    $stmt->execute();
    $stmt->close();

    $conn->commit(); // Commit the transaction

    sendJsonResponse([
        'success' => true,
        'message' => 'Username updated successfully!',
        'newUsername' => $newUsername
    ], 200);

} catch (Exception $e) {
    $conn->rollback(); // Rollback on any error
    error_log("Username change failed for user " . $userId . ": " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to change username: ' . $e->getMessage()], 500);
} finally {
    $conn->close();
}
?>