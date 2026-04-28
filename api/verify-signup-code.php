<?php
include '../config/session.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$code = trim($input['code'] ?? '');
$email = trim($input['email'] ?? '');

if (empty($code) || !preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['success' => false, 'message' => 'Invalid code format.']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

if (empty($_SESSION['temp_verification_email']) || empty($_SESSION['temp_verification_code'])) {
    echo json_encode(['success' => false, 'message' => 'No verification request found. Please send code again.']);
    exit;
}

if (strcasecmp($_SESSION['temp_verification_email'], $email) !== 0) {
    echo json_encode(['success' => false, 'message' => 'Email does not match the requested verification email.']);
    exit;
}

if (!hash_equals((string) $_SESSION['temp_verification_code'], (string) $code)) {
    echo json_encode(['success' => false, 'message' => 'Invalid verification code.']);
    exit;
}

$_SESSION['signup_email_verified'] = true;
$_SESSION['signup_verified_email'] = $email;

// Clear one-time code so it cannot be reused.
unset($_SESSION['temp_verification_code']);
unset($_SESSION['temp_verification_token']);

echo json_encode(['success' => true, 'message' => 'Email verified successfully!']);
?>
