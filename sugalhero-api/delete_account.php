<?php
// sugalhero-api/delete_account.php

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader
require_once __DIR__ . '/config.php';          // Configuration
require_once __DIR__ . '/database.php';        // Database connection
require_once __DIR__ . '/auth_helper.php';     // JWT functions

// Set content type to JSON and handle CORS

// Ensure it's a POST or DELETE request (POST is commonly used for form submissions)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected POST or DELETE.'], 405);
}

// Validate JWT token and get user ID
$userId = validateJwtToken(); // This function will exit if token is invalid/missing

// --- Security Note: For production, you might want to re-authenticate (e.g., require password again) here ---
// You would get the password from the request body and use password_verify against the stored hash.

// Connect to database
$conn = getDbConnection();

// Start a database transaction for atomicity
$conn->begin_transaction();

try {
    // Delete the user record
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("i", $userId);
    
    if (!$stmt->execute()) {
        throw new Exception('Database execution failed: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        // User not found (might have been deleted by another process, or token invalid)
        $conn->rollback();
        sendJsonResponse(['success' => false, 'message' => 'User not found or already deleted.'], 404);
    }

    $stmt->close();
    $conn->commit(); // Commit the transaction if successful

    sendJsonResponse([
        'success' => true,
        'message' => 'User account deleted successfully.'
    ], 200);

} catch (Exception $e) {
    $conn->rollback(); // Rollback on any error
    error_log("Account deletion failed for user " . $userId . ": " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to delete account: ' . $e->getMessage()], 500);
} finally {
    $conn->close();
}
?>