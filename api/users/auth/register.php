<?php
require 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$fullname = $data['fullname'] ?? '';
$email = $data['email'] ?? '';
$phone_no = $data['phone_no'] ?? '';
$password = $data['password'] ?? '';
$bank_name = $data['bank_name'] ?? '';
$account_name = $data['account_name'] ?? '';

if (!$fullname || !$email || !$phone_no || !$password || !$bank_name || !$account_name) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

$hashed_password = password_hash($password, PASSWORD_BCRYPT);
$email_verification_code = bin2hex(random_bytes(16));
$phone_verification_code = rand(100000, 999999);

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone_no = ?");
$stmt->execute([$email, $phone_no]);
if ($stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'User with this email or phone already exists']);
    exit;
}

$insert = $pdo->prepare("INSERT INTO users (fullname, email, phone_no, password, bank_name, account_name, email_verification_code, phone_verification_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$insert->execute([$fullname, $email, $phone_no, $hashed_password, $bank_name, $account_name, $email_verification_code, $phone_verification_code]);

// Simulate sending verification (you should replace with actual mail/SMS functions)
mail($email, "Verify your email", "Your verification code is: $email_verification_code");
file_put_contents("sms_log.txt", "Send this code to $phone_no: $phone_verification_code\n", FILE_APPEND);

echo json_encode(['status' => 'success', 'message' => 'Registration successful. Verification codes sent.']);
?>
