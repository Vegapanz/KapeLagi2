<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signin.php');
    exit;
}

$token = isset($_POST['token']) ? trim($_POST['token']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

function reset_password_strength_error($password) {
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password must contain at least one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        return 'Password must contain at least one lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'Password must contain at least one number';
    }
    if (!preg_match('/[!@#$%^&*]/', $password)) {
        return 'Password must contain at least one special character (!@#$%^&*)';
    }

    return '';
}

if ($token === '' || $password === '' || $password_confirm === '') {
    $params = ['token=' . urlencode($token)];
    if ($password === '') {
        $params[] = 'password_error=' . urlencode('Password is required');
    }
    if ($password_confirm === '') {
        $params[] = 'confirm_error=' . urlencode('Please confirm your password');
    }
    if ($password === '' && $password_confirm === '') {
        $params[] = 'error=' . urlencode('All fields are required');
    }
    header('Location: ../reset_password.php?' . implode('&', $params));
    exit;
}

if ($password !== $password_confirm) {
    header('Location: ../reset_password.php?token=' . urlencode($token) . '&confirm_error=' . urlencode('Passwords do not match'));
    exit;
}

$strengthError = reset_password_strength_error($password);
if ($strengthError !== '') {
    header('Location: ../reset_password.php?token=' . urlencode($token) . '&password_error=' . urlencode($strengthError));
    exit;
}

// Validate token
$stmt = $conn->prepare('SELECT email, expires_at FROM password_resets WHERE token = ? LIMIT 1');
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    header('Location: ../signin.php');
    exit;
}

$expires = new DateTime($row['expires_at']);
$now = new DateTime();
if ($expires < $now) {
    // expired
    // delete token and redirect
    $del = $conn->prepare('DELETE FROM password_resets WHERE token = ?');
    $del->bind_param('s', $token);
    $del->execute();
    $del->close();
    header('Location: ../forgot_password.php');
    exit;
}

$email = $row['email'];

// Update user password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$upd = $conn->prepare('UPDATE users SET password = ? WHERE email = ?');
$upd->bind_param('ss', $passwordHash, $email);
$upd->execute();
$upd->close();

// Remove any reset tokens for this email
$delAll = $conn->prepare('DELETE FROM password_resets WHERE email = ?');
$delAll->bind_param('s', $email);
$delAll->execute();
$delAll->close();

// Redirect to signin with a success flag
header('Location: ../signin.php?reset=1');
exit;
