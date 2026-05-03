<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/password_reset.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forgot_password.php');
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
if ($email === '') {
    header('Location: ../forgot_password.php');
    exit;
}

ensure_password_resets_table($conn);

// Attempt to find user (we will not reveal existence to client)
$stmt = $conn->prepare('SELECT id, name, email FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$user) {
    header('Location: ../forgot_password.php?error=' . urlencode('No account was found for that email address.'));
    exit;
}

$token = generate_reset_token();
$expires_at = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
$created_at = (new DateTime())->format('Y-m-d H:i:s');

// Remove existing tokens for this email and insert new one
$del = $conn->prepare('DELETE FROM password_resets WHERE email = ?');
$del->bind_param('s', $email);
$del->execute();
$del->close();

$ins = $conn->prepare('INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, ?)');
$ins->bind_param('ssss', $email, $token, $expires_at, $created_at);
$ins->execute();
$ins->close();

// Send the reset email for a real account.
$name = $user['name'] ?? '';
$mailRes = send_password_reset_email($email, $name, $token);

if (!$mailRes['sent']) {
    $deleteToken = $conn->prepare('DELETE FROM password_resets WHERE email = ?');
    $deleteToken->bind_param('s', $email);
    $deleteToken->execute();
    $deleteToken->close();

    header('Location: ../forgot_password.php?error=' . urlencode('We could not send the reset email right now. Please try again later.'));
    exit;
}

header('Location: ../forgot_password.php?sent=1');
exit;
