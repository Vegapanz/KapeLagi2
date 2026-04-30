<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/google_oauth.php';

if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

if (GOOGLE_OAUTH_CLIENT_ID === '' || GOOGLE_OAUTH_CLIENT_SECRET === '') {
    http_response_code(500);
    echo 'Google OAuth is not configured. Set the values in config/google.php or the GOOGLE_OAUTH_CLIENT_ID and GOOGLE_OAUTH_CLIENT_SECRET environment variables first.';
    exit;
}

$_SESSION['google_oauth_state'] = bin2hex(random_bytes(16));
$_SESSION['google_oauth_return_to'] = isset($_GET['return_to']) && $_GET['return_to'] !== ''
    ? google_oauth_sanitize_return_to((string) $_GET['return_to'])
    : '../index.php';

header('Location: ' . google_oauth_auth_url($_SESSION['google_oauth_state']));
exit;
