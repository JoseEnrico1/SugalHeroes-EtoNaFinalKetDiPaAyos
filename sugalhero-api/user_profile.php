<?php
// sugalhero-api/user_profile.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected GET.'], 405);
}

$userId = validateJwtToken();
if (!$userId) {
    sendJsonResponse(['success' => false, 'message' => 'Authentication failed. No user ID from token.'], 401);
}

$conn = null;
try {
    $conn = getDbConnection();
    // CORRECTED: Fetching 'currency_balance' instead of 'fiat_balance'
    $stmt = $conn->prepare("SELECT id, username, email, token_balance, currency_balance FROM users WHERE id = ?");

    if ($stmt === false) {
        error_log("Database Prepare Error in user_profile.php: " . $conn->error);
        sendJsonResponse(['success' => false, 'message' => 'Internal server error: Database statement preparation failed.'], 500);
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        sendJsonResponse(['success' => false, 'message' => 'User profile data not found for the authenticated user.'], 404);
    }

    $user['token_balance'] = (string)number_format((float)$user['token_balance'], 18, '.', '');
    // NEW: Format currency_balance for consistent output
    $user['currency_balance'] = (string)number_format((float)$user['currency_balance'], 2, '.', ''); 

    sendJsonResponse([
        'success' => true,
        'message' => 'User profile fetched successfully.',
        'user' => $user
    ], 200);

} catch (Exception $e) {
    error_log("Unhandled Exception in user_profile.php for user " . $userId . ": " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'An unexpected server error occurred: ' . $e->getMessage()], 500);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>