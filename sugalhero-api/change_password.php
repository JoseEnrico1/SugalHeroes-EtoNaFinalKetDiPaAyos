<?php
// sugalhero-api/change_password.php

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

// --- Input Validation for Password Change ---
if (!isset($data['old_password'], $data['new_password'], $data['confirm_new_password']) ||
    empty($data['old_password']) || empty($data['new_password']) || empty($data['confirm_new_password'])) {
    sendJsonResponse(['success' => false, 'message' => 'Old password, new password, and confirmation are required.'], 400);
}

$oldPassword = $data['old_password'];
$newPassword = $data['new_password'];
$confirmNewPassword = $data['confirm_new_password'];

// Basic password strength/match validation
if ($newPassword !== $confirmNewPassword) {
    sendJsonResponse(['success' => false, 'message' => 'New password and confirmation do not match.'], 400);
}
if (strlen($newPassword) < 8) { // Example: Minimum 8 characters
    sendJsonResponse(['success' => false, 'message' => 'New password must be at least 8 characters long.'], 400);
}
// You might add more complex password policies here (e.g., require numbers, symbols, etc.)

// Connect to database
$conn = getDbConnection();

// Start a database transaction for atomicity
$conn->begin_transaction();

try {
    // 1. Fetch current hashed password for verification (and lock row)
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? FOR UPDATE");
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        throw new Exception('User not found.'); // Should not happen after authentication
    }

    $storedHashedPassword = $user['password'];

    // 2. Verify old password
    if (!password_verify($oldPassword, $storedHashedPassword)) {
        $conn->rollback(); // Rollback transaction
        sendJsonResponse(['success' => false, 'message' => 'Incorrect old password.'], 401); // 401 Unauthorized
    }

    // 3. Hash the new password
    $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // 4. Update the password in the database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("si", $newHashedPassword, $userId);
    $stmt->execute();
    $stmt->close();

    $conn->commit(); // Commit the transaction if all steps succeeded

    sendJsonResponse([
        'success' => true,
        'message' => 'Password updated successfully!'
    ], 200);

} catch (Exception $e) {
    $conn->rollback(); // Rollback on any error
    error_log("Password change failed for user " . $userId . ": " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to change password: ' . $e->getMessage()], 500);
} finally {
    $conn->close();
}
?>