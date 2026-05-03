<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/../config/mail.php')) {
    require_once __DIR__ . '/../config/mail.php';
}

function ensure_password_resets_table($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        token VARCHAR(128) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME NOT NULL,
        INDEX(email),
        INDEX(token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $conn->query($sql);
}

function generate_reset_token() {
    return bin2hex(random_bytes(32));
}

function get_project_base_url() {
    $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/KapeLagi/index.php';
    $currentPath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $basePath = rtrim(str_replace('\\', '/', dirname($currentPath)), '/');

    return $scheme . '://' . $host . ($basePath === '' ? '' : $basePath);
}

function build_reset_link($token) {
    return get_project_base_url() . '/reset_password.php?token=' . urlencode($token);
}

function send_password_reset_email($email, $name, $token) {
    $resetLink = build_reset_link($token);
    $safeName = trim($name) === '' ? 'there' : $name;

    $subject = 'Reset your KapeLagi password';
    $message = "Hello {$safeName},\n\n"
        . "We received a request to reset the password for your KapeLagi account.\n\n"
        . "Click the link below to reset your password (link expires in 1 hour):\n\n"
        . $resetLink . "\n\n"
        . "If you didn't request this, you can safely ignore this email.\n\n- KapeLagi Team";

    $sent = false;
    $errorMessage = '';
    $mailer = new PHPMailer(true);

    try {
        if (defined('MAILER_USE_SMTP') && MAILER_USE_SMTP) {
            $smtpUser = defined('MAILER_USERNAME') ? trim((string) MAILER_USERNAME) : '';
            $smtpPass = defined('MAILER_PASSWORD') ? trim((string) MAILER_PASSWORD) : '';
            if ($smtpUser === '' || $smtpPass === '') {
                return ['sent' => false, 'link' => $resetLink, 'error' => 'SMTP misconfigured.'];
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
            $errorMessage = 'PHP mail() failed.';
        }
    }

    return ['sent' => $sent, 'link' => $resetLink, 'error' => $errorMessage];
}
