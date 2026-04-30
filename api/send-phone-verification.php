<?php
header('Content-Type: application/json');
include '../config/session.php';
include '../config/db.php';
include '../auth/email_verification.php';

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
$phone = trim($_POST['phone'] ?? '');

// Validate phone number (basic validation)
if (empty($phone) || !preg_match('/^09\d{9}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format (use 09xxxxxxxxx)']);
    exit;
}

// Get user data
$user_sql = "SELECT email, name FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = $user_result->fetch_assoc();
$user_stmt->close();

// Generate OTP code
$otp_code = generate_verification_code();

// Store in session temporarily
$_SESSION['pending_phone'] = $phone;
$_SESSION['pending_phone_otp'] = $otp_code;
$_SESSION['pending_phone_otp_time'] = time();

// Send OTP via email
$message = "Hello {$user['name']},\n\n"
    . "Your phone verification code for KapeLagi checkout is:\n\n"
    . "📱 {$otp_code}\n\n"
    . "This code will expire in 10 minutes.\n\n"
    . "If you didn't request this, please ignore this email.\n\n"
    . "- KapeLagi Team";

require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$mailer = new PHPMailer(true);

try {
    if (defined('MAILER_USE_SMTP') && MAILER_USE_SMTP) {
        $mailer->isSMTP();
        $mailer->Host = MAILER_HOST;
        $mailer->Port = MAILER_PORT;
        $mailer->SMTPAuth = true;
        $mailer->Username = MAILER_USERNAME;
        $mailer->Password = MAILER_PASSWORD;
        $mailer->SMTPSecure = MAILER_ENCRYPTION;
    }

    $mailer->setFrom(MAILER_FROM_ADDRESS, MAILER_FROM_NAME);
    $mailer->addAddress($user['email'], $user['name']);
    $mailer->Subject = 'Phone Verification Code for KapeLagi Checkout';
    $mailer->Body = $message;
    $mailer->isHTML(false);

    $mailer->send();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Verification code sent to your email',
        'phone' => substr_replace($phone, '****', 2, 7)  // Show partially masked phone
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send verification code']);
    exit;
}
