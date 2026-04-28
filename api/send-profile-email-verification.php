<?php
include '../config/session.php';
include '../config/db.php';
include '../auth/email_verification.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

$user_sql = 'SELECT id, name, email, pending_email, pending_email_verification_token, pending_email_verification_code FROM users WHERE id = ? LIMIT 1';
$user_stmt = $conn->prepare($user_sql);
if (!$user_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$current_email = trim((string) ($user['email'] ?? ''));
$pending_email = trim((string) ($user['pending_email'] ?? ''));

if ($email === '') {
    $email = $pending_email;
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if ($pending_email === '' && strcasecmp($current_email, $email) === 0) {
    echo json_encode(['success' => true, 'message' => 'This email is already verified on your account.']);
    exit;
}

$check_sql = 'SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1';
$check_stmt = $conn->prepare($check_sql);
if (!$check_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$check_stmt->bind_param('si', $email, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
if ($check_result->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'That email is already in use.']);
    exit;
}

ensure_user_verification_columns($conn);
$token = generate_verification_token();
$code = generate_verification_code();

$update_sql = "
    UPDATE users
    SET pending_email = ?,
        pending_email_verification_token = ?,
        pending_email_verification_code = ?,
        updated_at = NOW()
    WHERE id = ?
";
$update_stmt = $conn->prepare($update_sql);
if (!$update_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$update_stmt->bind_param('sssi', $email, $token, $code, $user_id);

if (!$update_stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to start email verification.']);
    exit;
}

$mailRes = send_verification_email($email, $user['name'] ?? 'there', $token, $code, true);

if (!empty($mailRes) && !empty($mailRes['sent'])) {
    echo json_encode(['success' => true, 'message' => 'Verification code sent to ' . htmlspecialchars($email)]);
    exit;
}

http_response_code(500);
$reason = !empty($mailRes['error']) ? $mailRes['error'] : 'Email delivery is unavailable in this environment.';
echo json_encode(['success' => false, 'message' => 'Could not send verification email. ' . $reason]);
