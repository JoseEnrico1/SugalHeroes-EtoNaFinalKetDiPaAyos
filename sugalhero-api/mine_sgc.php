<?php
// sugalhero-api/mine_sgc.php

// Required headers for CORS and JSON response
header("Access-Control-Allow-Origin: *"); // Adjust in production to specific frontend URL
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection and JWT validation utility
require_once 'config.php'; // Adjust path as needed for your DB connection and JWT functions

$response = []; // Prepare response array

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    // 1. Authenticate User
    $headers = getallheaders();
    $jwt = $headers['Authorization'] ?? '';
    $jwt = str_replace('Bearer ', '', $jwt);

    if (empty($jwt)) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Unauthorized: No token provided."]);
        exit();
    }

    try {
        // Assuming your config.php has a function to validate JWT and return user ID
        $user_id = validate_jwt($jwt); 
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Unauthorized: Invalid token."]);
            exit();
        }

        // 2. Implement Mining Logic (Random Chance)
        $mined_amount = 0.00;
        $chance = rand(1, 100); // Generate a random number between 1 and 100
        $win_chance = 10; // e.g., 10% chance to mine something (adjust as desired)

        if ($chance <= $win_chance) {
            // User gets lucky and mines some SGC
            $lucky_roll = rand(0, 1); // 0 for 0.001, 1 for 0.002
            $mined_amount = ($lucky_roll == 0) ? 0.001 : 0.002;
            
            // 3. Update User's SGC Balance in Database
            $conn->begin_transaction(); // Start transaction for atomicity

            $stmt = $conn->prepare("UPDATE users SET token_balance = token_balance + ? WHERE user_id = ?");
            // Use 'd' for double/float if your DB driver expects it, or 's' for string if BigInt handling is complex
            // For DECIMAL(25,18), it's often best to send as string to prevent precision loss.
            $mined_amount_str = number_format($mined_amount, 18, '.', ''); // Format to full precision
            $stmt->bind_param("sd", $mined_amount_str, $user_id); 
            
            if ($stmt->execute()) {
                $conn->commit(); // Commit transaction
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Congratulations! You mined " . number_format($mined_amount, 3) . " SGC!",
                    "mined_amount" => number_format($mined_amount, 3) // Return human-readable mined amount
                ]);
            } else {
                $conn->rollback(); // Rollback on error
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "Failed to update balance: " . $stmt->error]);
            }
            $stmt->close();

        } else {
            // No SGC mined this time
            http_response_code(200);
            echo json_encode([
                "success" => true, // Still success, just no mining reward
                "message" => "Better luck next time! No SGC mined this round.",
                "mined_amount" => 0.000
            ]);
        }

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