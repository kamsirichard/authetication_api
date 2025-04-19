<?php
require 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$phone_no = $data['phone_no'] ?? '';
$verification_code = $data['code'] ?? '';

if (!$phone_no || !$verification_code) {
    echo json_encode(['status' => 'error', 'message' => 'Phone number and verification code are required']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE phone_no = ? AND phone_verification_code = ?");
$stmt->execute([$phone_no, $verification_code]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid verification code or phone number']);
    exit;
}

$update = $pdo->prepare("UPDATE users SET phone_verified = 1 WHERE phone_no = ?");
$update->execute([$phone_no]);

echo json_encode(['status' => 'success', 'message' => 'Phone number verified successfully']);
?>
