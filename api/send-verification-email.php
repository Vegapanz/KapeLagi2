<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

include '../config/db.php';
include '../config/session.php';
include '../auth/email_verification.php';

// Check if email already exists in users table
$checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
if (!$checkStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$checkStmt->bind_param('s', $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

$checkStmt->close();

// Generate verification code and token
$token = generate_verification_token();
$code = generate_verification_code();

// Store in session for later use during signup
$_SESSION['temp_verification_email'] = $email;
$_SESSION['temp_verification_code'] = $code;
$_SESSION['temp_verification_token'] = $token;
$_SESSION['temp_verification_sent_at'] = time();
unset($_SESSION['signup_email_verified']);
unset($_SESSION['signup_verified_email']);

// Send verification email
$mailRes = send_verification_email($email, 'there', $token, $code, false);

if (!empty($mailRes) && !empty($mailRes['sent'])) {
    echo json_encode(['success' => true, 'message' => 'Verification code sent to ' . htmlspecialchars($email)]);
} else {
    http_response_code(500);
    $reason = !empty($mailRes['error']) ? $mailRes['error'] : 'Email delivery is unavailable in this environment.';
    echo json_encode(['success' => false, 'message' => 'Could not send verification email. ' . $reason]);
}
