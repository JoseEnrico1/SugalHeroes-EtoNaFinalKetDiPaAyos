<?php
// sugalhero-api/initiate_sgc_sale.php
// This API endpoint simulates the process of your platform selling/sending SGC to a user's MetaMask wallet
// after a presumed off-chain payment (e.g., fiat payment via Gcash).

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth_helper.php'; // For validateJwtToken() and sendJsonResponse()

// For interacting with Ethereum / Sepolia
// You would need a PHP Web3 library like web3-php or use Guzzle to call a blockchain node/Etherscan API
// For demonstration, we will simulate the blockchain transfer part.
// In a real app, this would involve your server signing a transaction with its private key.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected POST.'], 405);
}

// Validate JWT token to ensure a logged-in user is making the request
$userId = validateJwtToken();
if (!$userId) {
    sendJsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$amountSGC = $data['amountSGC'] ?? null;
$userMetamaskAddress = $data['metamaskAddress'] ?? null; // The user's connected MetaMask address

// Validate input
if (!is_numeric($amountSGC) || $amountSGC <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid SGC amount provided.'], 400);
}
if (empty($userMetamaskAddress) || !preg_match('/^0x[a-fA-F0-9]{40}$/', $userMetamaskAddress)) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid MetaMask address provided.'], 400);
}

$conn = getDbConnection();

// --- Step 1: Optional: Fetch user's internal ID based on MetaMask address if not provided by JWT (less common) ---
// For our flow, user ID comes from JWT, MetaMask address is provided from frontend.

try {
    // For a real system, here you would:
    // 1. Verify payment if it's fiat (e.g., check payment gateway webhook).
    // 2. Interact with the blockchain to send SGC from your platform's hot wallet to $userMetamaskAddress.
    //    This is the most complex part of a real system (managing private keys, gas, nonces).
    //    For this demo, we will SIMULATE the blockchain transfer and directly record it.

    // Simulate blockchain transfer (for demo purposes)
    $simulatedTxHash = '0x' . bin2hex(random_bytes(32)); // Generate a random hex string as a mock TxHash
    $status = 'completed'; // Assume immediate completion for demo

    // Record the SGC "sale" (platform sending tokens to user's MetaMask) in crypto_transactions
    $stmt = $conn->prepare("INSERT INTO crypto_transactions (user_id, token_id, type, amount, to_address, tx_hash, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        throw new Exception("Crypto transaction statement preparation failed: " . $conn->error);
    }

    // You need to decide on a token_id. If you only have SugalCoin, it's likely 1 or a specific ID you set.
    // Assuming SugalCoin's token_id is 1. Adjust if different.
    $sugalCoinTokenId = 1;
    $type = 'sale'; // Using 'sale' type to indicate platform selling/transferring SGC to user's wallet
    $amountFormatted = number_format($amountSGC, 18, '.', ''); // Ensure full precision for SGC

    $stmt->bind_param("iisdsss", $userId, $sugalCoinTokenId, $type, $amountFormatted, $userMetamaskAddress, $simulatedTxHash, $status);

    if ($stmt->execute()) {
        sendJsonResponse([
            'success' => true,
            'message' => "Successfully purchased (sent) {$amountSGC} SGC to your MetaMask. Transaction ID: " . $simulatedTxHash,
            'transactionId' => $conn->insert_id,
            'txHash' => $simulatedTxHash
        ], 200);
    } else {
        throw new Exception("Failed to record SGC sale in database: " . $stmt->error);
    }

} catch (Exception $e) {
    error_log("Error initiating SGC sale for user " . $userId . ": " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to process SGC purchase: ' . $e->getMessage()], 500);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>