<?php
// sugalhero-api/sgc_deposit.php
// This API endpoint handles the deposit of SGC from a user's MetaMask wallet to their
// internal platform balance. It includes a SIMULATED blockchain verification.

// Enable full error reporting for debugging. REMOVE IN PRODUCTION!
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth_helper.php';

// --- Configuration (must match frontend's deposit address and token details) ---
// This is the platform's deposit address where users send SGC.
// MUST match PLATFORM_SGC_DEPOSIT_ADDRESS in purchase_sgc.php
// IMPORTANT: Replace with the SAME address you used in your JavaScript.
const PLATFORM_SGC_DEPOSIT_ADDRESS_BACKEND = '0xEa3B92DB904919A09Ff86E75C2Fa8699dd675149'; // <<< YOUR DEPOSIT HOTWALLET ADDRESS
const SUGALCOIN_CONTRACT_ADDRESS_BACKEND = '0xA14BD2ba7E888eaC70F815F24081e453befEf634'; // Your SGC contract address
const SUGALCOIN_TOKEN_ID = 1; // Assuming SugalCoin's token_id in your crypto_tokens table is 1. Adjust if different.


// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected POST.'], 405);
}

// Validate JWT token
$userId = validateJwtToken();
if (!$userId) {
    sendJsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
}

// Get raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$amount = $data['amount'] ?? null;
$txHash = $data['txHash'] ?? null;
$fromAddress = $data['fromAddress'] ?? null; // User's MetaMask address that sent the transaction

// Basic validation for received data
if (!is_numeric($amount) || $amount <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid deposit amount.'], 400);
}
if (empty($txHash) || !preg_match('/^0x[a-fA-F0-9]{64}$/', $txHash)) { // Basic hash validation
    sendJsonResponse(['success' => false, 'message' => 'Invalid transaction hash provided.'], 400);
}
if (empty($fromAddress) || !preg_match('/^0x[a-fA-F0-9]{40}$/', $fromAddress)) { // Basic address validation
    sendJsonResponse(['success' => false, 'message' => 'Invalid sender address provided.'], 400);
}

$conn = null; // Initialize connection to null

try {
    $conn = getDbConnection(); // Get database connection

    // --- SIMULATED BLOCKCHAIN VERIFICATION (FOR DEMO ONLY) ---
    // In a real system, you would perform actual blockchain verification here.
    // For this demo, we assume success if basic input validation passed.
    $isTxVerifiedOnBlockchain = true; // SIMULATED SUCCESS

    if (!$isTxVerifiedOnBlockchain) {
        // This block will not be hit with current simulation, but crucial for real verification
        sendJsonResponse(['success' => false, 'message' => 'Blockchain transaction verification failed.'], 400);
    }

    // --- Check for Duplicate Transaction Hash (Crucial for idempotency) ---
    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM crypto_transactions WHERE tx_hash = ? AND status = 'completed'");
    if ($stmtCheck === false) {
        throw new Exception("Duplicate check statement preparation failed: " . $conn->error);
    }
    $stmtCheck->bind_param("s", $txHash);
    $stmtCheck->execute();
    $stmtCheck->bind_result($count);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($count > 0) {
        sendJsonResponse(['success' => false, 'message' => 'This transaction has already been processed.'], 409); // 409 Conflict
    }

    // --- Start Database Transaction for Atomicity ---
    // Ensures either both insert and update succeed, or both fail.
    $conn->begin_transaction();

    // --- Prepare values for binding ---
    // Explicitly casting and storing in new variables to help with bind_param's strictness
    $insertUserId = (int)$userId;
    $insertTokenId = (int)SUGALCOIN_TOKEN_ID;
    $insertType = 'deposit'; // Fixed type for deposit
    $insertAmountFormatted = number_format($amount, 18, '.', ''); // String for DECIMAL type
    $insertFromAddress = $fromAddress;
    $insertTxHash = $txHash;
    $insertStatus = 'completed'; // Fixed status for demo


    // --- Record Deposit in crypto_transactions table ---
    $stmtInsert = $conn->prepare("INSERT INTO crypto_transactions (user_id, token_id, type, amount, from_address, tx_hash, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmtInsert === false) {
        throw new Exception("Insert transaction statement preparation failed: " . $conn->error);
    }
    // "iisdsss" : integer, integer, string, double/decimal, string, string, string
    $stmtInsert->bind_param("iisdsss", $insertUserId, $insertTokenId, $insertType, $insertAmountFormatted, $insertFromAddress, $insertTxHash, $insertStatus);

    if (!$stmtInsert->execute()) {
        throw new Exception("Failed to record SGC deposit transaction: " . $stmtInsert->error);
    }
    $stmtInsert->close();

    // --- Prepare values for binding (for update) ---
    // Using distinct variable names here again for extreme clarity to bind_param
    $updateAmount = (float)$amount; // Cast to float for mathematical operation
    $updateUserId = (int)$userId;

    // --- Update User's Internal token_balance in 'users' table ---
    $stmtUpdate = $conn->prepare("UPDATE users SET token_balance = token_balance + ? WHERE id = ?");
    if ($stmtUpdate === false) {
        throw new Exception("Update user balance statement preparation failed: " . $conn->error);
    }
    // "di" : double/decimal, integer
    $stmtUpdate->bind_param("di", $updateAmount, $updateUserId); // Line 92, using our new explicit variables

    if (!$stmtUpdate->execute()) {
        throw new Exception("Failed to update user's token balance: " . $stmtUpdate->error);
    }
    $stmtUpdate->close();

    $conn->commit(); // Commit the database transaction

    sendJsonResponse(['success' => true, 'message' => "{$amount} SGC successfully deposited to your internal balance!"], 200);

} catch (Exception $e) {
    if ($conn && $conn->in_transaction) { // Check if a transaction is active before rollback
        $conn->rollback(); // Rollback on error
    }
    error_log("SGC Deposit Error for user {$userId}: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to process SGC deposit: ' . $e->getMessage()], 500);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>