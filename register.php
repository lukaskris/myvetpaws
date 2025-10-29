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
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

$hashed = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashed]);
    echo json_encode(['status' => 'success', 'message' => 'User registered']);
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'UNIQUE')) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>