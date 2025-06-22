<?php
// Include necessary files
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader
require_once __DIR__ . '/config.php';          // Configuration
require_once __DIR__ . '/database.php';        // Database connection
require_once __DIR__ . '/auth_helper.php';     // JWT functions

// Set content type to JSON and handle CORS

// Ensure it's a POST request for balance modifications
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected POST.'], 405);
}

// Validate JWT token and get user ID
$userId = validateJwtToken(); // This function will exit if token is invalid/missing

// Get raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// --- Input Validation for Withdrawal ---
if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid withdrawal amount. Amount must be a positive number.'], 400);
}

$withdrawAmount = (float)$data['amount'];

// Connect to database
$conn = getDbConnection();

// Start a database transaction for atomicity
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

    // 2. Check for sufficient balance before withdrawal
    if ($currentBalance < $withdrawAmount) {
        $conn->rollback(); // Rollback transaction as balance is insufficient
        sendJsonResponse(['success' => false, 'message' => 'Insufficient balance for withdrawal.'], 400);
    }

    // 3. Deduct withdrawal amount from balance
    $newBalance = $currentBalance - $withdrawAmount;
    $stmt = $conn->prepare("UPDATE users SET currency_balance = ? WHERE id = ?");
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    // Use 's' for DECIMAL type to maintain precision
    $newBalanceStr = number_format($newBalance, 2, '.', ''); // Format to 2 decimal places as a string
    $stmt->bind_param("si", $newBalanceStr, $userId);
    $stmt->execute();
    $stmt->close();

    // 4. Record the withdrawal transaction
    $stmt = $conn->prepare(
        "INSERT INTO transactions (user_id, type, amount, description, status)
         VALUES (?, ?, ?, ?, ?)"
    );
    if ($stmt === false) {
        throw new Exception($conn->error);
    }

    $transactionType = 'withdrawal';
    $description = 'Funds withdrawn';
    $status = 'completed'; // Withdrawals are typically completed immediately

    // Use 's' for amount (DECIMAL)
    $withdrawAmountStr = number_format($withdrawAmount, 2, '.', ''); // Format to 2 decimal places as a string

    $stmt->bind_param("issss", $userId, $transactionType, $withdrawAmountStr, $description, $status);
    $stmt->execute();
    $withdrawalTransactionId = $conn->insert_id; // Get the ID of the new transaction
    $stmt->close();

    $conn->commit(); // Commit the transaction if all steps succeeded

    sendJsonResponse([
        'success' => true,
        'message' => 'Funds withdrawn successfully!',
        'newBalance' => $newBalance,
        'withdrawalTransactionId' => $withdrawalTransactionId
    ], 200);

} catch (Exception $e) {
    $conn->rollback(); // Rollback on any error
    error_log("Withdrawal failed for user " . $userId . ": " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to withdraw funds: ' . $e->getMessage()], 500);
} finally {
    $conn->close();
}
?>