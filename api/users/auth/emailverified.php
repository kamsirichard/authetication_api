<?php
require 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$verification_code = $data['code'] ?? '';

if (!$email || !$verification_code) {
    echo json_encode(['status' => 'error', 'message' => 'Email and verification code are required']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND email_verification_code = ?");
$stmt->execute([$email, $verification_code]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid verification code or email']);
    exit;
}

$update = $pdo->prepare("UPDATE users SET email_verified = 1 WHERE email = ?");
$update->execute([$email]);

echo json_encode(['status' => 'success', 'message' => 'Email verified successfully']);
?>
