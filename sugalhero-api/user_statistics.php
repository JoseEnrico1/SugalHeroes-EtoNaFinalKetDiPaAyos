<?php
// Include necessary files
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader
require_once __DIR__ . '/config.php';          // Configuration
require_once __DIR__ . '/database.php';        // Database connection
require_once __DIR__ . '/auth_helper.php';     // JWT functions

// Set content type to JSON and handle CORS

// Ensure it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected GET.'], 405);
}

// Validate JWT token and get user ID
$userId = validateJwtToken(); // This function will exit if token is invalid/missing

// Connect to database
$conn = getDbConnection();

try {
    $stats = [];

    // --- 1. Total Bets Placed & Total Wagered ---
    $stmt = $conn->prepare("SELECT COUNT(id) AS total_bets_placed, SUM(amount) AS total_wagered FROM transactions WHERE user_id = ? AND type = 'bet'");
    if ($stmt === false) throw new Exception($conn->error);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['total_bets_placed'] = (int)($row['total_bets_placed'] ?? 0);
    $stats['total_wagered'] = (float)($row['total_wagered'] ?? 0.00);
    $stmt->close();

    // --- 2. Total Wins ---
    $stmt = $conn->prepare("SELECT COUNT(id) AS total_wins, SUM(amount) AS total_payout FROM transactions WHERE user_id = ? AND type = 'win'");
    if ($stmt === false) throw new Exception($conn->error);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['total_wins'] = (int)($row['total_wins'] ?? 0);
    $stats['total_payout'] = (float)($row['total_payout'] ?? 0.00);
    $stmt->close();

    // --- 3. Net Profit/Loss ---
    // This is typically (Total Payouts) - (Total Wagered)
    $stats['net_profit_loss'] = $stats['total_payout'] - $stats['total_wagered'];

    // --- 4. Win Rate ---
    $stats['win_rate'] = ($stats['total_bets_placed'] > 0) ? ($stats['total_wins'] / $stats['total_bets_placed']) * 100 : 0;

    // --- 5. Biggest Win ---
    $stmt = $conn->prepare("SELECT MAX(amount) AS biggest_win_amount FROM transactions WHERE user_id = ? AND type = 'win'");
    if ($stmt === false) throw new Exception($conn->error);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['biggest_win'] = (float)($row['biggest_win_amount'] ?? 0.00);
    $stmt->close();

    // --- 6. Favorite Game (Most bets placed on) ---
    $stmt = $conn->prepare("SELECT g.game_name, COUNT(t.id) AS bet_count FROM transactions t LEFT JOIN games g ON t.game_id = g.id WHERE t.user_id = ? AND t.type = 'bet' AND t.game_id IS NOT NULL GROUP BY g.game_name ORDER BY bet_count DESC LIMIT 1");
    if ($stmt === false) throw new Exception($conn->error);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['favorite_game'] = $row['game_name'] ?? 'N/A';
    $stmt->close();

    // --- 7. Wagered Over Time (Last 7 Days for Chart) ---
    $wageredOverTime = [];
    $today = new DateTime();
    for ($i = 6; $i >= 0; $i--) { // Last 7 days including today
        $date = (clone $today)->modify("-$i days");
        $formattedDate = $date->format('Y-m-d');
        $wageredOverTime[$formattedDate] = 0.00; // Initialize with 0
    }

    $stmt = $conn->prepare("SELECT DATE(created_at) AS bet_date, SUM(amount) AS daily_wagered FROM transactions WHERE user_id = ? AND type = 'bet' AND created_at >= CURDATE() - INTERVAL 6 DAY GROUP BY bet_date ORDER BY bet_date ASC");
    if ($stmt === false) throw new Exception($conn->error);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $wageredOverTime[$row['bet_date']] = (float)$row['daily_wagered'];
    }
    $stmt->close();
    $stats['wagered_over_time_labels'] = array_keys($wageredOverTime);
    $stats['wagered_over_time_data'] = array_values($wageredOverTime);

    // --- 8. Bets by Game Type (for Chart) ---
    $stmt = $conn->prepare("SELECT g.game_name, COUNT(t.id) AS bet_count FROM transactions t LEFT JOIN games g ON t.game_id = g.id WHERE t.user_id = ? AND t.type = 'bet' AND t.game_id IS NOT NULL GROUP BY g.game_name ORDER BY bet_count DESC");
    if ($stmt === false) throw new Exception($conn->error);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $betsByGameTypeLabels = [];
    $betsByGameTypeData = [];
    while ($row = $result->fetch_assoc()) {
        $betsByGameTypeLabels[] = $row['game_name'];
        $betsByGameTypeData[] = (int)$row['bet_count'];
    }
    $stmt->close();
    $stats['bets_by_game_type_labels'] = $betsByGameTypeLabels;
    $stats['bets_by_game_type_data'] = $betsByGameTypeData;


    sendJsonResponse([
        'success' => true,
        'message' => 'User statistics fetched successfully.',
        'statistics' => $stats
    ], 200);

} catch (Exception $e) {
    error_log("Failed to fetch user statistics for user " . $userId . ": " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to fetch statistics: ' . $e->getMessage()], 500);
} finally {
    $conn->close();
}
?>