<?php
header('Content-Type: application/json');
include '../config/session.php';
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = get_user_id();
$input = json_decode(file_get_contents('php://input'), true);
$otp_code = trim($input['code'] ?? '');

// Validate OTP format
if (empty($otp_code) || !preg_match('/^\d{6}$/', $otp_code)) {
    echo json_encode(['success' => false, 'message' => 'Invalid code format']);
    exit;
}

// Check if there's a pending phone verification
if (empty($_SESSION['pending_phone_otp']) || empty($_SESSION['pending_phone'])) {
    echo json_encode(['success' => false, 'message' => 'No pending phone verification. Please start again.']);
    exit;
}

// Check if OTP expired (10 minutes)
$otp_time = $_SESSION['pending_phone_otp_time'] ?? 0;
if (time() - $otp_time > 600) {
    unset($_SESSION['pending_phone_otp']);
    unset($_SESSION['pending_phone']);
    unset($_SESSION['pending_phone_otp_time']);
    echo json_encode(['success' => false, 'message' => 'Verification code expired. Please request a new one.']);
    exit;
}

// Verify OTP
$stored_otp = $_SESSION['pending_phone_otp'];
$phone = $_SESSION['pending_phone'];

if ($otp_code !== $stored_otp) {
    echo json_encode(['success' => false, 'message' => 'Incorrect verification code']);
    exit;
}

// Update user's phone number
$update_sql = "UPDATE users SET phone = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param('si', $phone, $user_id);

if (!$update_stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update phone number']);
    exit;
}

// Clear pending verification
unset($_SESSION['pending_phone_otp']);
unset($_SESSION['pending_phone']);
unset($_SESSION['pending_phone_otp_time']);

// Mark session as phone verified for this checkout
$_SESSION['phone_verified'] = true;
$_SESSION['verified_phone'] = $phone;

$update_stmt->close();

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Phone number verified successfully',
    'phone' => $phone
]);
exit;
