<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php'; // Composer's autoloader

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * Generates a JWT token for a given user ID.
 * @param int $userId The ID of the user.
 * @return string The generated JWT token.
 */
function generateJwtToken($userId) {
    $issuedAt = time();
    $expirationTime = $issuedAt + JWT_EXPIRATION_SECONDS; // Token valid for 1 hour

    $payload = [
        'iat' => $issuedAt, // Issued at
        'exp' => $expirationTime, // Expiration time
        'sub' => $userId // Subject (user ID)
    ];

    return JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);
}

/**
 * Validates a JWT token from the Authorization header.
 * @return int|false The user ID if token is valid, false otherwise.
 */
function validateJwtToken() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Authorization token not provided or malformed.'
        ], 401);
        return false; // This line won't be reached due to sendJsonResponse exiting
    }

    $jwt = $matches[1];

    try {
        $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALGORITHM));
        return $decoded->sub; // Return user ID
    } catch (ExpiredException $e) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Token has expired.'
        ], 401);
    } catch (SignatureInvalidException $e) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Invalid token signature.'
        ], 401);
    } catch (Exception $e) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Invalid token: ' . $e->getMessage()
        ], 401);
    }
    return false;
}