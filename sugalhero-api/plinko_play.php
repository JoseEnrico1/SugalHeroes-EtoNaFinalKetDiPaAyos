<?php
// sugalhero-api/plinko_play.php
// Handles Plinko game play, including bet deduction, payout, and SGC mining reward.

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth_helper.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected POST.'], 405);
}

// Validate JWT token
$userId = validateJwtToken();
if (!$userId) {
    sendJsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// --- Input Validation ---
$betAmount = $data['amount'] ?? null;
$currencyType = $data['currency_type'] ?? null; // 'fiat' or 'token'
$riskLevel = $data['risk_level'] ?? null;     // 'low', 'medium', 'high'
$gameId = $data['game_id'] ?? null;           // Should be 2 for Plinko
$isMetamaskConnected = $data['is_metamask_connected'] ?? false; // Boolean from frontend

if (!is_numeric($betAmount) || $betAmount <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid bet amount.'], 400);
}
if (!in_array($currencyType, ['fiat', 'token'])) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid currency type.'], 400);
}
if (!in_array($riskLevel, ['low', 'medium', 'high'])) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid risk level.'], 400);
}
// Assuming game_id 2 is indeed Plinko (from your 'games' table)
if ((int)$gameId !== 2) { // Cast to int for strict comparison
    sendJsonResponse(['success' => false, 'message' => 'Invalid game ID for Plinko.'], 400);
}

$conn = null;
try {
    $conn = getDbConnection();

    // --- Fetch User's Current Balances ---
    // CORRECTED: Fetching 'currency_balance' instead of 'fiat_balance'
    $stmtUser = $conn->prepare("SELECT token_balance, currency_balance FROM users WHERE id = ? FOR UPDATE"); // FOR UPDATE to lock row
    if ($stmtUser === false) { throw new Exception("User balance statement preparation failed: " . $conn->error); }
    $stmtUser->bind_param("i", $userId);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    $user = $resultUser->fetch_assoc();
    $stmtUser->close();

    if (!$user) {
        sendJsonResponse(['success' => false, 'message' => 'User not found.'], 404);
    }

    $currentSgcBalance = (float)$user['token_balance'];
    $currentFiatBalance = (float)$user['currency_balance']; // NEW: Use currency_balance

    // --- Check Sufficient Balance & Deduct Bet ---
    $conn->begin_transaction(); // Start transaction for atomicity

    if ($currencyType === 'token') {
        if ($currentSgcBalance < $betAmount) {
            $conn->rollback();
            sendJsonResponse(['success' => false, 'message' => 'Insufficient SGC balance.'], 400);
        }
        $newSgcBalance = $currentSgcBalance - $betAmount;
        $newFiatBalance = $currentFiatBalance; // Fiat balance unchanged
    } else { // 'fiat'
        // NEW: Deduct from currency_balance for fiat bets
        if ($currentFiatBalance < $betAmount) {
            $conn->rollback();
            sendJsonResponse(['success' => false, 'message' => 'Insufficient Fiat balance.'], 400);
        }
        $newFiatBalance = $currentFiatBalance - $betAmount;
        $newSgcBalance = $currentSgcBalance; // SGC balance unchanged
    }

    // --- Plinko Game Logic (Server-Side) ---
    $multipliers = [
        'low' =>    [0.5, 0.8, 1, 1.2, 1.5, 1.2, 1, 0.8, 0.5],
        'medium' => [0.4, 0.7, 1, 1.5, 2, 1.5, 1, 0.7, 0.4],
        'high' =>   [0.2, 0.5, 0.8, 1.5, 3, 1.5, 0.8, 0.5, 0.2]
    ];
    $selectedMultipliers = $multipliers[$riskLevel] ?? $multipliers['low']; // Default to low if invalid risk
    $slotIndex = mt_rand(0, count($selectedMultipliers) - 1); // Randomly determine landing slot index
    $multiplier = $selectedMultipliers[$slotIndex];

    $payoutAmount = $betAmount * $multiplier;
    $outcome = ($payoutAmount > $betAmount) ? 'win' : (($payoutAmount < $betAmount) ? 'loss' : 'draw');


    $miningRewardAmount = 0.0; // Initialize mining reward

    // --- Mining Feature Logic ---
    // Only if SGC is used and MetaMask is connected
    if ($currencyType === 'token' && $isMetamaskConnected) {
        $randMining = mt_rand(1, 1000); // Random number between 1 and 1000

        if ($randMining === 1) { // 1 in 1000 chance for 0.02 SGC
            $miningRewardAmount = 0.02;
            $newSgcBalance += $miningRewardAmount;
            $stmtMining = $conn->prepare("INSERT INTO crypto_transactions (user_id, token_id, type, amount, status, description) VALUES (?, ?, ?, ?, ?, ?)");
            if($stmtMining === false) throw new Exception("Mining reward (rare) statement failed: " . $conn->error);
            $desc = "Mining Reward (Rare)";
            $status = "completed";
            $miningAmountForDb = number_format($miningRewardAmount, 18, '.', ''); // Ensure precision for DB
            $stmtMining->bind_param("iisdss", (int)$userId, (int)SUGALCOIN_TOKEN_ID, 'mining_reward', $miningAmountForDb, $status, $desc);
            $stmtMining->execute();
            $stmtMining->close();
        } elseif ($randMining >= 2 && $randMining <= 5) { // 1 in 200 chance for 0.01 SGC (2-5/1000 = 4/1000 = 1/250, so combined with 1st, it's about 1/200 total)
            $miningRewardAmount = 0.01;
            $newSgcBalance += $miningRewardAmount;
            $stmtMining = $conn->prepare("INSERT INTO crypto_transactions (user_id, token_id, type, amount, status, description) VALUES (?, ?, ?, ?, ?, ?)");
            if($stmtMining === false) throw new Exception("Mining reward (common) statement failed: " . $conn->error);
            $desc = "Mining Reward (Common)";
            $status = "completed";
            $miningAmountForDb = number_format($miningRewardAmount, 18, '.', ''); // Ensure precision for DB
            $stmtMining->bind_param("iisdss", (int)$userId, (int)SUGALCOIN_TOKEN_ID, 'mining_reward', $miningAmountForDb, $status, $desc);
            $stmtMining->execute();
            $stmtMining->close();
        }
    }


    // --- Update User Balances in Database ---
    $stmtUpdateBalance = $conn->prepare("UPDATE users SET token_balance = ?, currency_balance = ? WHERE id = ?"); // Corrected column name
    if ($stmtUpdateBalance === false) { throw new Exception("Update balance statement preparation failed: " . $conn->error); }

    // Prepare for binding
    $finalSgcBalance = number_format($newSgcBalance, 18, '.', ''); // String for SGC precision
    $finalFiatBalance = number_format($newFiatBalance, 2, '.', ''); // String for Fiat precision
    $bindUserId = (int)$userId; // Ensure this is explicitly int

    // Bind parameters: 'sds' (string for token_balance, double for currency_balance, integer for user_id)
    $stmtUpdateBalance->bind_param("sdi", $finalSgcBalance, $finalFiatBalance, $bindUserId); // sdi is string, double, int

    if (!$stmtUpdateBalance->execute()) {
        throw new Exception("Failed to update user balances: " . $stmtUpdateBalance->error);
    }
    $stmtUpdateBalance->close();


    // --- Record Game History ---
    $stmtGameHistory = $conn->prepare(
        "INSERT INTO game_history (user_id, game_id, bet_amount_fiat, payout_fiat, bet_amount_token, payout_token, currency_type, outcome, details)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if ($stmtGameHistory === false) { throw new Exception("Game history statement preparation failed: " . $conn->error); }

    $betAmountFiat = ($currencyType === 'fiat') ? (float)$betAmount : 0.0; // Ensure float
    $payoutFiat = ($currencyType === 'fiat') ? (float)$payoutAmount : 0.0; // Ensure float
    $betAmountToken = ($currencyType === 'token') ? (float)$betAmount : 0.0; // Ensure float
    $payoutToken = ($currencyType === 'token') ? (float)$payoutAmount : 0.0; // Ensure float

    $details = json_encode([
        'risk_level' => $riskLevel,
        'slot_index' => $slotIndex,
        'multiplier' => $multiplier,
        'mining_reward' => (float)$miningRewardAmount // Include mining reward in history details
    ]);

    // "iddiddsss" : user_id(int), game_id(int), bet_fiat(double), payout_fiat(double), bet_token(double), payout_token(double), currency_type(string), outcome(string), details(string/json)
    $stmtGameHistory->bind_param(
        "iddiddsss",
        (int)$userId,
        (int)$gameId,
        $betAmountFiat,
        $payoutFiat,
        $betAmountToken,
        $payoutToken,
        $currencyType,
        $outcome,
        $details
    );

    if (!$stmtGameHistory->execute()) {
        throw new Exception("Failed to record game history: " . $stmtGameHistory->error);
    }
    $stmtGameHistory->close();

    $conn->commit(); // Commit the transaction if all successful

    // --- Send Success Response ---
    sendJsonResponse([
        'success' => true,
        'message' => 'Game played successfully.',
        'outcome' => $outcome,
        'result_slot_index' => $slotIndex,
        'multiplier' => $multiplier,
        'payout_amount' => (float)$payoutAmount, // Send float back for frontend display
        'mining_reward_amount' => (float)$miningRewardAmount, // Send float reward
        'new_fiat_balance' => (float)$newFiatBalance, // Send updated fiat balance
        'new_sgc_balance' => (float)$newSgcBalance // Send updated SGC balance
    ], 200);

} catch (Exception $e) {
    if ($conn && $conn->in_transaction) {
        $conn->rollback();
    }
    error_log("Plinko Play Error for user {$userId}: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Game play failed: ' . $e->getMessage()], 500);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>