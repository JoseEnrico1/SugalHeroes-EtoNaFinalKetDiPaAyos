<?php
// sugalhero-api/crypto_transactions.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config.php'; // For DB connection and JWT validation

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $headers = getallheaders();
    $jwt = $headers['Authorization'] ?? '';
    $jwt = str_replace('Bearer ', '', $jwt);

    if (empty($jwt)) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Unauthorized: No token provided."]);
        exit();
    }

    try {
        $user_id = validate_jwt($jwt);
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Unauthorized: Invalid token."]);
            exit();
        }

        // Fetch crypto transactions for the user
        // Joining with crypto_tokens to get symbol and decimals
        $stmt = $conn->prepare("
            SELECT
                ct.type,
                ct.amount,
                ct.from_address,
                ct.to_address,
                ct.tx_hash,
                ct.status,
                ct.created_at,
                t.symbol AS token_symbol,
                t.decimals AS token_decimals
            FROM crypto_transactions ct
            JOIN crypto_tokens t ON ct.token_id = t.token_id
            WHERE ct.user_id = ?
            ORDER BY ct.created_at DESC
            LIMIT 50 -- Limit to recent transactions
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = [];

        while ($row = $result->fetch_assoc()) {
            // Send raw amount and decimals for frontend formatting
            $row['amount_raw'] = $row['amount']; 
            unset($row['amount']); // Remove formatted amount if you had it
            $row['decimals'] = $row['token_decimals'];
            unset($row['token_decimals']);

            // Format date for display
            $row['date'] = (new DateTime($row['created_at']))->format('M d, Y h:i:s A');
            unset($row['created_at']);

            $transactions[] = $row;
        }

        http_response_code(200);
        echo json_encode(["success" => true, "transactions" => $transactions]);

        $stmt->close();

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
    }
    $conn->close();

} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method Not Allowed."]);
}
?>