<?php
// sugalhero-api/user_transactions.php

require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader
require_once __DIR__ . '/config.php';          // Configuration
require_once __DIR__ . '/database.php';        // Database connection
require_once __DIR__ . '/auth_helper.php';     // JWT functions (contains validateJwtToken() and sendJsonResponse())

// Set content type to JSON and handle CORS (handled by header in config.php)

// Ensure it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected GET.'], 405);
}

// Validate JWT token and get user ID
$userId = validateJwtToken();
if (!$userId) {
    sendJsonResponse(['success' => false, 'message' => 'Authentication failed. No user ID from token.'], 401);
}

$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = isset($_GET['offset']) && is_numeric($_GET['offset']) ? (int)$_GET['offset'] : 0;

$conn = getDbConnection();

$allTransactions = [];

try {
    // --- 1. Fetch from your existing 'transactions' table (Fiat/Internal Currency) ---
    // UPDATED: Changed g.name to g.game_name
    $stmtFiatInternal = $conn->prepare(
        "SELECT
            t.id AS transaction_id,
            t.type,
            t.amount,
            t.description,
            t.status,
            t.created_at,
            NULL AS tx_hash,
            'fiat' AS currency_type,
            g.game_name AS game_name, -- UPDATED: Used g.game_name
            NULL AS outcome
        FROM transactions t
        LEFT JOIN games g ON t.game_id = g.id
        WHERE t.user_id = ?"
    );
    if ($stmtFiatInternal === false) {
        throw new Exception("Fiat/Internal transactions statement preparation failed: " . $conn->error);
    }
    $stmtFiatInternal->bind_param("i", $userId);
    $stmtFiatInternal->execute();
    $resultFiatInternal = $stmtFiatInternal->get_result();

    while ($row = $resultFiatInternal->fetch_assoc()) {
        $row['amount'] = (string)number_format((float)$row['amount'], 2, '.', '');
        $allTransactions[] = $row;
    }
    $stmtFiatInternal->close();

    // --- 2. Fetch from 'crypto_transactions' table (Bought SGC / Deposits/Withdrawals/Transfers) ---
    $stmtCrypto = $conn->prepare(
        "SELECT
            transaction_id,
            type,
            amount,
            NULL AS description,
            status,
            created_at,
            tx_hash,
            'token' AS currency_type,
            NULL AS game_name,
            NULL AS outcome
        FROM crypto_transactions
        WHERE user_id = ?"
    );
    if ($stmtCrypto === false) {
        throw new Exception("Crypto transactions statement preparation failed: " . $conn->error);
    }
    $stmtCrypto->bind_param("i", $userId);
    $stmtCrypto->execute();
    $resultCrypto = $stmtCrypto->get_result();

    while ($row = $resultCrypto->fetch_assoc()) {
        $row['amount'] = (string)number_format((float)$row['amount'], 18, '.', '');
        $allTransactions[] = $row;
    }
    $stmtCrypto->close();

    // --- 3. Fetch from 'game_history' table (Betted SGC / Game Wins/Losses) ---
    // UPDATED: Used g.game_name in the pre-fetching logic
    $stmtGameHistory = $conn->prepare(
        "SELECT
            history_id AS transaction_id,
            game_id,
            bet_amount_token,
            payout_token,
            currency_type,
            outcome,
            bet_time AS created_at,
            NULL AS type,
            NULL AS description,
            NULL AS status,
            NULL AS tx_hash
        FROM game_history
        WHERE user_id = ? AND currency_type = 'token'"
    );
    if ($stmtGameHistory === false) {
        throw new Exception("Game history statement preparation failed: " . $conn->error);
    }
    $stmtGameHistory->bind_param("i", $userId);
    $stmtGameHistory->execute();
    $resultGameHistory = $stmtGameHistory->get_result();

    // Pre-fetch game names using 'game_name' column
    $gameNames = [];
    $stmtGamesList = $conn->query("SELECT id, game_name FROM games"); // UPDATED: Selected game_name
    if ($stmtGamesList) {
        while ($game = $stmtGamesList->fetch_assoc()) {
            $gameNames[$game['id']] = $game['game_name']; // UPDATED: Used game_name
        }
    }

    while ($row = $resultGameHistory->fetch_assoc()) {
        $game_name = $gameNames[$row['game_id']] ?? 'Unknown Game';
        $transaction_amount = 0;
        $transaction_type_label = '';

        switch ($row['outcome']) {
            case 'win':
                $transaction_amount = (float)$row['payout_token'] - (float)$row['bet_amount_token'];
                $transaction_type_label = 'win';
                break;
            case 'loss':
                $transaction_amount = -(float)$row['bet_amount_token'];
                $transaction_type_label = 'bet';
                break;
            case 'draw':
                $transaction_amount = 0;
                $transaction_type_label = 'draw';
                break;
            default:
                $transaction_amount = (float)$row['payout_token'] - (float)$row['bet_amount_token'];
                $transaction_type_label = 'game_event';
                break;
        }

        $allTransactions[] = [
            'transaction_id' => $row['transaction_id'],
            'type' => $transaction_type_label,
            'amount' => (string)number_format($transaction_amount, 18, '.', ''),
            'description' => $row['outcome'] . ' in ' . $game_name,
            'status' => 'completed',
            'created_at' => $row['created_at'],
            'tx_hash' => NULL,
            'currency_type' => 'token',
            'game_name' => $game_name,
            'outcome' => $row['outcome']
        ];
    }
    $stmtGameHistory->close();

    // --- Sort all transactions by created_at in descending order (most recent first) ---
    usort($allTransactions, function($a, $b) {
        return strtotime($b['created_at']) - strtotime(is_array($a['created_at']) ? $a['created_at'][0] : $a['created_at']);
    });


    // Apply limit and offset for pagination (after sorting combined results)
    $paginatedTransactions = array_slice($allTransactions, $offset, $limit);

    sendJsonResponse([
        'success' => true,
        'message' => 'Transactions fetched successfully.',
        'currentPage' => floor($offset / $limit) + 1,
        'limit' => $limit,
        'totalTransactions' => count($allTransactions),
        'totalPages' => ceil(count($allTransactions) / $limit),
        'transactions' => $paginatedTransactions
    ], 200);

} catch (Exception $e) {
    error_log("Failed to fetch transactions for user " . $userId . ": " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to fetch transactions: ' . $e->getMessage()], 500);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>