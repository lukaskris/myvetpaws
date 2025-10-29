<?php
require 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email and password required']);
    exit;
}

$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    unset($user['password']);
    echo json_encode(['status' => 'success', 'message' => 'Login success', 'user' => $user]);
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
}
?>