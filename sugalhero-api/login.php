<?php
// Include necessary files
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader
require_once __DIR__ . '/config.php';          // Configuration (DB credentials, JWT secret)
require_once __DIR__ . '/database.php';        // Database connection function
require_once __DIR__ . '/auth_helper.php';     // JWT generation/validation functions

// Set content type to JSON and handle CORS (defined in config.php)

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

// Get raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['username'], $data['password']) ||
    empty($data['username']) || empty($data['password'])) {
    sendJsonResponse(['success' => false, 'message' => 'Username and password are required.'], 400);
}

$username = $data['username'];
$password = $data['password'];

// Connect to database
$conn = getDbConnection();

// Fetch user by username
$stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
if ($stmt === false) {
    handleDbError($conn);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Verify user and password
if (!$user || !password_verify($password, $user['password'])) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid username or password.'], 401); // 401 Unauthorized
}

// Generate JWT token
$token = generateJwtToken($user['id']);

// Send successful login response with token
sendJsonResponse([
    'success' => true,
    'message' => 'Login successful!',
    'token' => $token,
    'userId' => $user['id'],
    'username' => $user['username']
], 200); // 200 OK
?>