<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/google_oauth.php';
require_once __DIR__ . '/email_verification.php';

function google_oauth_callback_error(string $message): void
{
    http_response_code(400);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Google Sign In</title><style>body{font-family:Arial,sans-serif;padding:2rem;background:#f7f1e8;color:#3d2817}a{color:#3d2817}</style></head><body><h1>Google sign in failed</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p><p><a href="../signin.php">Back to sign in</a></p></body></html>';
    exit;
}

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if (empty($_GET['code']) || empty($_GET['state'])) {
    google_oauth_callback_error('Missing authorization response from Google.');
}

if (empty($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], (string) $_GET['state'])) {
    unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_return_to']);
    google_oauth_callback_error('Google returned an invalid security state. Please try again.');
}

unset($_SESSION['google_oauth_state']);

$tokenResult = google_oauth_exchange_code((string) $_GET['code']);
if (empty($tokenResult['success'])) {
    google_oauth_callback_error($tokenResult['error'] ?? 'Could not complete Google authentication.');
}

$userInfoResult = google_oauth_fetch_userinfo($tokenResult['data']['access_token']);
if (empty($userInfoResult['success'])) {
    google_oauth_callback_error($userInfoResult['error'] ?? 'Could not read the Google profile.');
}

$googleUser = $userInfoResult['data'];
$googleId = trim((string) ($googleUser['sub'] ?? ''));
$email = trim((string) ($googleUser['email'] ?? ''));
$name = trim((string) ($googleUser['name'] ?? 'Google User'));
$avatarUrl = trim((string) ($googleUser['picture'] ?? ''));

if ($googleId === '' || $email === '') {
    google_oauth_callback_error('Google did not return the required profile details.');
}

if (isset($googleUser['email_verified']) && !$googleUser['email_verified']) {
    google_oauth_callback_error('Google account email is not verified.');
}

ensure_user_verification_columns($conn);
ensure_google_oauth_columns($conn);

$conn->begin_transaction();

try {
    $user = null;

    $findByGoogleId = $conn->prepare('SELECT id, name, email, role FROM users WHERE google_id = ? LIMIT 1');
    $findByGoogleId->bind_param('s', $googleId);
    $findByGoogleId->execute();
    $googleResult = $findByGoogleId->get_result();
    if ($googleResult && $googleResult->num_rows === 1) {
        $user = $googleResult->fetch_assoc();
    }

    if ($user === null) {
        $findByEmail = $conn->prepare('SELECT id, name, email, role, google_id FROM users WHERE email = ? LIMIT 1');
        $findByEmail->bind_param('s', $email);
        $findByEmail->execute();
        $emailResult = $findByEmail->get_result();
        if ($emailResult && $emailResult->num_rows === 1) {
            $user = $emailResult->fetch_assoc();
        }
    }

    if ($user !== null) {
        $updateSql = 'UPDATE users SET name = COALESCE(NULLIF(?, ""), name), email_verified_at = COALESCE(email_verified_at, NOW()), google_id = ?, oauth_provider = ?, oauth_avatar_url = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
        $updateStmt = $conn->prepare($updateSql);
        $provider = 'google';
        $updateStmt->bind_param('ssssi', $name, $googleId, $provider, $avatarUrl, $user['id']);
        $updateStmt->execute();

        $userId = (int) $user['id'];
        $role = $user['role'] ?? 'customer';
        $displayName = $name !== '' ? $name : (string) $user['name'];
    } else {
        $randomPassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
        $provider = 'google';
        $termsVersion = 'google-oauth';
        $insertSql = 'INSERT INTO users (name, email, password, email_verified_at, google_id, oauth_provider, oauth_avatar_url, terms_accepted_at, terms_version) VALUES (?, ?, ?, NOW(), ?, ?, ?, NOW(), ?)';
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param('sssssss', $name, $email, $randomPassword, $googleId, $provider, $avatarUrl, $termsVersion);
        $insertStmt->execute();

        $userId = $conn->insert_id;
        $role = 'customer';
        $displayName = $name;
    }

    $conn->commit();

    login_user($userId, $displayName, $email, $role);

    $returnTo = isset($_SESSION['google_oauth_return_to']) ? trim((string) $_SESSION['google_oauth_return_to']) : '../index.php';
    if ($returnTo === '' || preg_match('/^[a-z][a-z0-9+.-]*:/i', $returnTo) || str_starts_with($returnTo, '//')) {
        $returnTo = '../index.php';
    }
    unset($_SESSION['google_oauth_return_to']);

    if (is_string($returnTo) && $returnTo !== '') {
        header('Location: ' . $returnTo);
    } else {
        header('Location: ../index.php');
    }
    exit;
} catch (Throwable $throwable) {
    $conn->rollback();
    google_oauth_callback_error('Google sign in could not be completed.');
}
