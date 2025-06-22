<?php
require_once __DIR__ . '/config.php'; // Include configuration

function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        // Log the error and send a generic response
        error_log("Database connection failed: " . $conn->connect_error);
        sendJsonResponse([
            'success' => false,
            'message' => 'Failed to connect to the database.'
        ], 500);
    }

    return $conn;
}