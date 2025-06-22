<?php
// Include necessary files
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader
require_once __DIR__ . '/config.php';          // Configuration
require_once __DIR__ . '/database.php';        // Database connection
require_once __DIR__ . '/auth_helper.php';     // JWT functions

// Set content type to JSON and handle CORS

// Ensure it's a POST request for actions like placing a bet
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected POST.'], 405);
}

// Validate JWT token and get user ID
$userId = validateJwtToken(); // This function will exit if token is invalid/missing

// Get raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// --- Input Validation for Bet ---
if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid bet amount.'], 400);
}

$betAmount = (float)$data['amount']; // Ensure it's a numeric type
$gameId = $data['game_id'] ?? null; // Optional: Link to a specific game
$betDetails = $data['details'] ?? null; // Optional: Store specific bet details (e.g., choice, odds)

// Connect to database
$conn = getDbConnection();

// Start a database transaction for atomicity
// This ensures either both balance update and transaction insert succeed, or both fail.
$conn->begin_transaction();

try {
    // 1. Get current user balance (and lock row for update)
    $stmt = $conn->prepare("SELECT currency_balance FROM users WHERE id = ? FOR UPDATE"); // FOR UPDATE locks the row
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

    $currentBalance = (float)$user['currency_balance'];

    // 2. Check for sufficient balance
    if ($currentBalance < $betAmount) {
        $conn->rollback(); // Rollback transaction as balance is insufficient
        sendJsonResponse(['success' => false, 'message' => 'Insufficient balance.'], 400);
    }

    // 3. Deduct bet amount from balance
    $newBalance = $currentBalance - $betAmount;
    $stmt = $conn->prepare("UPDATE users SET currency_balance = ? WHERE id = ?");
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    // Use 's' for DECIMAL type to maintain precision
    $newBalanceStr = number_format($newBalance, 2, '.', ''); // Format to 2 decimal places as a string
    $stmt->bind_param("si", $newBalanceStr, $userId);
    $stmt->execute();
    $stmt->close();

    // 4. Record the bet transaction
    $stmt = $conn->prepare(
        "INSERT INTO transactions (user_id, type, amount, description, game_id, status)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    if ($stmt === false) {
        throw new Exception($conn->error);
    }

    $transactionType = 'bet';
    $description = 'Placed bet';
    $status = 'pending'; // Bet outcome is pending until resolved

    // Use 's' for amount (DECIMAL) and game_id (if nullable or string)
    // If game_id is INT, use 'i'. Assuming it might be null or string for now.
    $betAmountStr = number_format($betAmount, 2, '.', ''); // Format to 2 decimal places as a string

    // Adjust bind_param based on actual game_id column type (INT for 'i', VARCHAR for 's')
    if (is_numeric($gameId)) { // Assuming game_id in DB is INT
        $stmt->bind_param("isdids", $userId, $transactionType, $betAmountStr, $description, $gameId, $status);
    } else { // If game_id can be null or a string in DB
        $stmt->bind_param("isdsis", $userId, $transactionType, $betAmountStr, $description, $gameId, $status); // Adjusted to handle gameId as string/null
    }


    $stmt->execute();
    $betTransactionId = $conn->insert_id; // Get the ID of the bet transaction
    $stmt->close();

    $conn->commit(); // Commit the transaction if all steps succeeded

    sendJsonResponse([
        'success' => true,
        'message' => 'Bet placed successfully!',
        'newBalance' => $newBalance,
        'betTransactionId' => $betTransactionId
    ], 200);

} catch (Exception $e) {
    $conn->rollback(); // Rollback on any error
    error_log("Bet placement failed for user " . $userId . ": " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to place bet: ' . $e->getMessage()], 500);
} finally {
    $conn->close();
}
?>