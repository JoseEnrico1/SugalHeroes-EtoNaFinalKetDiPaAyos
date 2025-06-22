<?php
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

// --- Input Validation for Bet Resolution ---
if (!isset($data['bet_transaction_id']) || !is_numeric($data['bet_transaction_id']) ||
    !isset($data['outcome']) || !in_array($data['outcome'], ['win', 'loss'])) {
    sendJsonResponse(['success' => false, 'message' => 'Bet transaction ID and outcome (win/loss) are required.'], 400);
}

$betTransactionId = (int)$data['bet_transaction_id'];
$outcome = $data['outcome']; // 'win' or 'loss'
$payoutAmount = (float)($data['payout_amount'] ?? 0); // Amount to add if 'win'

// Connect to database
$conn = getDbConnection();

// Start a database transaction for atomicity
$conn->begin_transaction();

try {
    // 1. Fetch the original bet transaction details
    // --- MODIFIED: Select game_id as well from original bet ---
    $stmt = $conn->prepare("SELECT user_id, amount, status, game_id FROM transactions WHERE id = ? FOR UPDATE");
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("i", $betTransactionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $betTransaction = $result->fetch_assoc();
    $stmt->close();

    if (!$betTransaction) {
        throw new Exception('Original bet transaction not found.');
    }
    if ($betTransaction['user_id'] != $userId) {
        throw new Exception('Unauthorized: Bet does not belong to this user.'); // Security check!
    }
    if ($betTransaction['status'] !== 'pending') {
        throw new Exception('Bet has already been resolved or is not pending.');
    }

    $originalBetAmount = (float)$betTransaction['amount']; // The amount user originally bet
    $originalGameId = $betTransaction['game_id']; // Get the game_id from the original bet

    // 2. Update user's balance if it's a win
    $balanceChange = 0.00;
    $description = 'Bet resolved: ' . $outcome;

    if ($outcome === 'win') {
        $balanceChange = $payoutAmount;
        $description = 'Bet won. Payout: ' . number_format($payoutAmount, 2);

        // Fetch current balance to calculate new balance
        $stmt = $conn->prepare("SELECT currency_balance FROM users WHERE id = ? FOR UPDATE");
        if ($stmt === false) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userBalanceRow = $result->fetch_assoc();
        $stmt->close();

        if (!$userBalanceRow) {
            throw new Exception('User balance not found during payout.');
        }
        $currentBalance = (float)$userBalanceRow['currency_balance'];
        $newBalance = $currentBalance + $balanceChange;

        $stmt = $conn->prepare("UPDATE users SET currency_balance = ? WHERE id = ?");
        if ($stmt === false) {
            throw new Exception($conn->error);
        }
        $newBalanceStr = number_format($newBalance, 2, '.', '');
        $stmt->bind_param("si", $newBalanceStr, $userId);
        $stmt->execute();
        $stmt->close();
    } else { // outcome is 'loss'
        $description = 'Bet lost.';
    }


    // 3. Add a separate transaction for 'win' (or just update original bet's description for loss)
    if ($outcome === 'win') {
        // --- MODIFIED: Include game_id when inserting 'win' transaction ---
        $stmt = $conn->prepare(
            "INSERT INTO transactions (user_id, type, amount, description, reference_id, status, game_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        if ($stmt === false) {
            throw new Exception($conn->error);
        }
        $winType = 'win';
        $payoutAmountStr = number_format($payoutAmount, 2, '.', '');
        $winStatus = 'completed';
        // Use originalGameId for the new win transaction
        $stmt->bind_param("isdsisi", $userId, $winType, $payoutAmountStr, $description, $betTransactionId, $winStatus, $originalGameId);
        // Note: The original bind_param was isdsis (6 params for 6 ?), now it's isdsisi (7 params for 7 ?)
        $stmt->execute();
        $stmt->close();
    }

    // Finally, mark the original bet transaction as completed/resolved.
    $stmt = $conn->prepare("UPDATE transactions SET status = ?, description = ? WHERE id = ?"); // Added description update for loss
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    $resolvedStatus = 'completed'; // Mark original bet as completed
    $stmt->bind_param("ssi", $resolvedStatus, $description, $betTransactionId); // Bind status, description, id
    $stmt->execute();
    $stmt->close();


    $conn->commit(); // Commit the transaction if all steps succeeded

    sendJsonResponse([
        'success' => true,
        'message' => 'Bet resolved successfully!',
        'outcome' => $outcome,
        'payoutAmount' => $payoutAmount,
        'betTransactionId' => $betTransactionId
    ], 200);

} catch (Exception $e) {
    $conn->rollback(); // Rollback on any error
    error_log("Bet resolution failed for user " . $userId . " (Bet ID: " . $betTransactionId . "): " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to resolve bet: ' . $e->getMessage()], 500);
} finally {
    $conn->close();
}
?>