<?php

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'sugalheroes'); // <--- IMPORTANT: Change this
define('DB_PASSWORD', 'arjen'); // <--- IMPORTANT: Change this
define('DB_NAME', 'sugalheroes');     // <--- IMPORTANT: Change this

// JWT configuration
define('JWT_SECRET', '2de34e8766fa739875dc6f80c5d5d9685c24d60778c83b9961495fa9e13e04ca'); // <--- IMPORTANT: Change this (generate a long, random string)
define('JWT_ALGORITHM', 'HS256'); // Recommended algorithm
define('JWT_EXPIRATION_SECONDS', 3600); // Token valid for 1 hour (3600 seconds)

// Set content type for JSON responses
header('Content-Type: application/json');

// Allow cross-origin requests (for your HTML/CSS/JS game on a different domain/port)
// IMPORTANT: In a production environment, restrict this to your actual game's domain.
header('Access-Control-Allow-Origin: *'); // Allows all origins
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS requests (preflight requests for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to send JSON responses
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Function to handle database errors
function handleDbError($conn) {
    // Log the error for debugging purposes (check your PHP error logs or web server error logs)
    error_log("Database Error: " . $conn->error);
    sendJsonResponse([
        'success' => false,
        'message' => 'An internal server error occurred.',
        // 'error' => $conn->error // <<< UNCOMMENT FOR DEVELOPMENT to see actual DB errors. COMMENT OUT FOR PRODUCTION!
    ], 500);
}