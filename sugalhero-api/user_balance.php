<?php
// Include necessary files
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader
require_once __DIR__ . '/config.php';          // Configuration (DB credentials, JWT secret)
require_once __DIR__ . '/database.php';        // Database connection function
require_once __DIR__ . '/auth_helper.php';     // JWT generation/validation functions

// Set content type to JSON and handle CORS (defined in config.php)

// Ensure it's a GET request for fetching data
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected GET.'], 405);
}

// Validate JWT token and get user ID
$userId = validateJwtToken(); // This function will exit if token is invalid/missing

// Connect to database
$conn = getDbConnection();

// Fetch user balance
$stmt = $conn->prepare("SELECT currency_balance FROM users WHERE id = ?");
if ($stmt === false) {
    handleDbError($conn);
}
$stmt->bind_param("i", $userId); // 'i' for integer
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    // This case should ideally not be hit if authentication worked correctly,
    // but it's a good safeguard for a user ID that somehow doesn't exist.
    sendJsonResponse(['success' => false, 'message' => 'User not found.'], 404);
}

// Send successful response with balance
sendJsonResponse([
    'success' => true,
    'message' => 'User balance fetched successfully.',
    'balance' => (float)$user['currency_balance'] // Cast to float for JSON numeric type
], 200);
?>