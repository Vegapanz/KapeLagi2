<?php

// Configure SMTP here for local development when environment variables are unavailable.
// Leave username/password empty if you prefer to provide them via environment variables.
$localUseSmtp = true;
$localHost = 'smtp.gmail.com';
$localPort = 587;
$localEncryption = 'tls';
$localUsername = 'kapelagidasma@gmail.com';
$localPassword = 'bnls hbxt bkga jdjr';
$localFromAddress = 'kapelagidasma@gmail.com';
$localFromName = 'KapeLagi';

$readMailerValue = static function (string $key, $fallback = '') {
	$value = getenv($key);
	if ($value !== false && $value !== '') {
		return $value;
	}
	if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
		return $_ENV[$key];
	}
	if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
		return $_SERVER[$key];
	}
	return $fallback;
};

$envUseSmtp = $readMailerValue('MAILER_USE_SMTP', $localUseSmtp ? 'true' : 'false');
$envHost = $readMailerValue('MAILER_HOST', $localHost);
$envPort = $readMailerValue('MAILER_PORT', (string) $localPort);
$envEncryption = $readMailerValue('MAILER_ENCRYPTION', $localEncryption);
$envUsername = $readMailerValue('MAILER_USERNAME', $localUsername);
$envPassword = $readMailerValue('MAILER_PASSWORD', $localPassword);
$envFromAddress = $readMailerValue('MAILER_FROM_ADDRESS', $localFromAddress);
$envFromName = $readMailerValue('MAILER_FROM_NAME', $localFromName);

define('MAILER_USE_SMTP', $envUseSmtp === false ? true : filter_var($envUseSmtp, FILTER_VALIDATE_BOOLEAN));
define('MAILER_HOST', $envHost !== false && $envHost !== '' ? $envHost : 'smtp.gmail.com');
define('MAILER_PORT', $envPort !== false && $envPort !== '' ? (int) $envPort : 587);
define('MAILER_ENCRYPTION', $envEncryption !== false && $envEncryption !== ''
	? $envEncryption
	: PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS);
define('MAILER_USERNAME', $envUsername !== false ? $envUsername : '');
define('MAILER_PASSWORD', $envPassword !== false ? $envPassword : '');
define('MAILER_FROM_ADDRESS', $envFromAddress !== false && $envFromAddress !== ''
	? $envFromAddress
	: (MAILER_USERNAME !== '' ? MAILER_USERNAME : 'no-reply@example.com'));
define('MAILER_FROM_NAME', $envFromName !== false && $envFromName !== '' ? $envFromName : 'KapeLagi');
