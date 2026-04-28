<?php
include '../config/session.php';
include '../config/db.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$code = trim($input['code'] ?? '');

if (empty($code) || !preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['success' => false, 'message' => 'Invalid code format.']);
    exit;
}

// Get user's pending email and code
$user_sql = "SELECT pending_email, pending_email_verification_code FROM users WHERE id = ? AND pending_email IS NOT NULL";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No pending email verification.']);
    exit;
}

$user = $user_result->fetch_assoc();
$stored_code = $user['pending_email_verification_code'] ?? '';
$new_email = $user['pending_email'] ?? '';

if ($code !== $stored_code) {
    echo json_encode(['success' => false, 'message' => 'Incorrect verification code.']);
    exit;
}

// Update email and clear pending fields
$update_sql = "
    UPDATE users
    SET email = ?, email_verified_at = NOW(), pending_email = NULL, pending_email_verification_token = NULL, pending_email_verification_code = NULL
    WHERE id = ?
";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param('si', $new_email, $user_id);

if ($update_stmt->execute()) {
    // Update session
    $_SESSION['user_email'] = $new_email;
    echo json_encode(['success' => true, 'message' => 'Email verified successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Unable to verify email. Please try again.']);
}
?>
