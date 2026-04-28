<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/../config/mail.php')) {
    require_once __DIR__ . '/../config/mail.php';
}

function ensure_user_verification_columns($conn) {
    static $checked = false;
    if ($checked) {
        return;
    }

    $requiredColumns = [
        'email_verified_at' => "ALTER TABLE users ADD COLUMN email_verified_at DATETIME NULL AFTER password",
        'email_verification_token' => "ALTER TABLE users ADD COLUMN email_verification_token VARCHAR(64) NULL AFTER email_verified_at",
        'email_verification_code' => "ALTER TABLE users ADD COLUMN email_verification_code VARCHAR(6) NULL AFTER email_verification_token",
        'pending_email' => "ALTER TABLE users ADD COLUMN pending_email VARCHAR(100) NULL AFTER email_verification_code",
        'pending_email_verification_token' => "ALTER TABLE users ADD COLUMN pending_email_verification_token VARCHAR(64) NULL AFTER pending_email",
        'pending_email_verification_code' => "ALTER TABLE users ADD COLUMN pending_email_verification_code VARCHAR(6) NULL AFTER pending_email_verification_token",
        'terms_accepted_at' => "ALTER TABLE users ADD COLUMN terms_accepted_at DATETIME NULL AFTER pending_email_verification_code",
        'terms_version' => "ALTER TABLE users ADD COLUMN terms_version VARCHAR(20) NULL AFTER terms_accepted_at"
    ];

    foreach ($requiredColumns as $columnName => $alterSql) {
        $checkSql = "SHOW COLUMNS FROM users LIKE '" . $conn->real_escape_string($columnName) . "'";
        $result = $conn->query($checkSql);
        if ($result && $result->num_rows === 0) {
            $conn->query($alterSql);
        }
    }

    $checked = true;
}

function generate_verification_token() {
    return bin2hex(random_bytes(32));
}

function generate_verification_code() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function get_base_url() {
    $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/KapeLagi/index.php';
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    return $scheme . '://' . $host . ($basePath === '' ? '' : $basePath);
}

function build_verification_link($token) {
    return get_base_url() . '/verify-email.php?token=' . urlencode($token);
}

function send_verification_email($email, $name, $token, $code = null, $is_email_change = false) {
    $verificationLink = build_verification_link($token);
    $safeName = trim($name) === '' ? 'there' : $name;

    if ($code !== null && $is_email_change) {
        $subject = 'Verify Your Email Change at KapeLagi';
        $message = "Hello {$safeName},\n\n"
            . "You requested to change your email address on your KapeLagi account.\n\n"
            . "Your verification code is: {$code}\n\n"
            . "Enter this code in the verification modal on your profile page to confirm the change.\n\n"
            . "If you did not request this change, please ignore this email.\n\n"
            . "- KapeLagi Team";
    } elseif ($code !== null) {
        $subject = 'Verify Your KapeLagi Account';
        $message = "Hello {$safeName},\n\n"
            . "Thanks for signing up at KapeLagi. Welcome!\n\n"
            . "Your verification code is: {$code}\n\n"
            . "Enter this code to complete your sign-up and start ordering.\n\n"
            . "If you did not create this account, please ignore this email.\n\n"
            . "- KapeLagi Team";
    } else {
        $subject = 'Verify Your KapeLagi Account';
        $message = "Hello {$safeName},\n\n"
            . "Thanks for signing up at KapeLagi. Please verify your email by clicking the link below:\n\n"
            . $verificationLink . "\n\n"
            . "If you did not create this account, you can ignore this email.\n\n"
            . "- KapeLagi Team";
    }

    $sent = false;
    $errorMessage = '';
    $mailer = new PHPMailer(true);

    try {
        if (defined('MAILER_USE_SMTP') && MAILER_USE_SMTP) {
            $smtpUser = defined('MAILER_USERNAME') ? trim((string) MAILER_USERNAME) : '';
            $smtpPass = defined('MAILER_PASSWORD') ? trim((string) MAILER_PASSWORD) : '';
            if ($smtpUser === '' || $smtpPass === '') {
                return [
                    'sent' => false,
                    'link' => $verificationLink,
                    'error' => 'SMTP is enabled but MAILER_USERNAME or MAILER_PASSWORD is missing.'
                ];
            }

            $mailer->isSMTP();
            $mailer->Host = defined('MAILER_HOST') ? MAILER_HOST : 'smtp.gmail.com';
            $mailer->SMTPAuth = true;
            $mailer->Username = $smtpUser;
            $mailer->Password = $smtpPass;
            $mailer->SMTPSecure = defined('MAILER_ENCRYPTION') ? MAILER_ENCRYPTION : PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->Port = defined('MAILER_PORT') ? (int) MAILER_PORT : 587;
        }

        $fromAddress = defined('MAILER_FROM_ADDRESS') ? MAILER_FROM_ADDRESS : 'no-reply@kapelagi.local';
        $fromName = defined('MAILER_FROM_NAME') ? MAILER_FROM_NAME : 'KapeLagi';

        $mailer->setFrom($fromAddress, $fromName);
        $mailer->addAddress($email, $safeName);
        $mailer->Subject = $subject;
        $mailer->isHTML(true);
        $mailer->Body = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        $mailer->AltBody = $message;

        $sent = $mailer->send();
    } catch (Exception $e) {
        $sent = false;
        $errorMessage = $e->getMessage();
    }

    if (!$sent && function_exists('mail')) {
        $headers = [
            'From: no-reply@kapelagi.local',
            'Reply-To: no-reply@kapelagi.local',
            'X-Mailer: PHP/' . phpversion()
        ];

        $sent = @mail($email, $subject, $message, implode("\r\n", $headers));
        if (!$sent && $errorMessage === '') {
            $errorMessage = 'PHP mail() failed. SMTP is likely not configured in this environment.';
        }
    }

    return [
        'sent' => $sent,
        'link' => $verificationLink,
        'error' => $errorMessage
    ];
}
