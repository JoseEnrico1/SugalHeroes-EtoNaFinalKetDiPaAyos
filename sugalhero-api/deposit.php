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

// --- Input Validation for Deposit ---
if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid deposit amount. Amount must be a positive number.'], 400);
}

$depositAmount = (float)$data['amount'];

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

    // 2. Add deposit amount to balance
    $newBalance = $currentBalance + $depositAmount;
    $stmt = $conn->prepare("UPDATE users SET currency_balance = ? WHERE id = ?");
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    // Use 's' for DECIMAL type to maintain precision
    $newBalanceStr = number_format($newBalance, 2, '.', ''); // Format to 2 decimal places as a string
    $stmt->bind_param("si", $newBalanceStr, $userId);
    $stmt->execute();
    $stmt->close();

    // 3. Record the deposit transaction
    $stmt = $conn->prepare(
        "INSERT INTO transactions (user_id, type, amount, description, status)
         VALUES (?, ?, ?, ?, ?)"
    );
    if ($stmt === false) {
        throw new Exception($conn->error);
    }

    $transactionType = 'deposit';
    $description = 'Funds deposited';
    $status = 'completed'; // Deposits are typically completed immediately

    // Use 's' for amount (DECIMAL)
    $depositAmountStr = number_format($depositAmount, 2, '.', ''); // Format to 2 decimal places as a string

    $stmt->bind_param("issss", $userId, $transactionType, $depositAmountStr, $description, $status);
    $stmt->execute();
    $depositTransactionId = $conn->insert_id; // Get the ID of the new transaction
    $stmt->close();

    $conn->commit(); // Commit the transaction if all steps succeeded

    sendJsonResponse([
        'success' => true,
        'message' => 'Funds deposited successfully!',
        'newBalance' => $newBalance,
        'depositTransactionId' => $depositTransactionId
    ], 200);

} catch (Exception $e) {
    $conn->rollback(); // Rollback on any error
    error_log("Deposit failed for user " . $userId . ": " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to deposit funds: ' . $e->getMessage()], 500);
} finally {
    $conn->close();
}
?>