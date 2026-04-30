<?php

$googleConfigFile = __DIR__ . '/google.php';
if (file_exists($googleConfigFile)) {
    require_once $googleConfigFile;
}

if (!function_exists('google_oauth_env')) {
    function google_oauth_env(string $key, string $fallback = ''): string
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }

        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string) $_ENV[$key];
        }

        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return (string) $_SERVER[$key];
        }

        $localKeyMap = [
            'GOOGLE_OAUTH_CLIENT_ID' => 'localGoogleOauthClientId',
            'GOOGLE_OAUTH_CLIENT_SECRET' => 'localGoogleOauthClientSecret',
            'GOOGLE_OAUTH_REDIRECT_URI' => 'localGoogleOauthRedirectUri',
        ];

        if (isset($localKeyMap[$key])) {
            $localKey = $localKeyMap[$key];
            if (isset($GLOBALS[$localKey]) && $GLOBALS[$localKey] !== '') {
                return (string) $GLOBALS[$localKey];
            }
        }

        return $fallback;
    }
}

if (!function_exists('google_oauth_base_url')) {
    function google_oauth_base_url(): string
    {
        $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        return $scheme . '://' . $host . ($basePath === '' ? '' : $basePath);
    }
}

if (!function_exists('google_oauth_redirect_uri')) {
    function google_oauth_redirect_uri(): string
    {
        return google_oauth_env('GOOGLE_OAUTH_REDIRECT_URI', google_oauth_base_url() . '/auth/google_callback.php');
    }
}

if (!defined('GOOGLE_OAUTH_CLIENT_ID')) {
    define('GOOGLE_OAUTH_CLIENT_ID', google_oauth_env('GOOGLE_OAUTH_CLIENT_ID', ''));
}

if (!defined('GOOGLE_OAUTH_CLIENT_SECRET')) {
    define('GOOGLE_OAUTH_CLIENT_SECRET', google_oauth_env('GOOGLE_OAUTH_CLIENT_SECRET', ''));
}

if (!defined('GOOGLE_OAUTH_AUTH_ENDPOINT')) {
    define('GOOGLE_OAUTH_AUTH_ENDPOINT', 'https://accounts.google.com/o/oauth2/v2/auth');
}

if (!defined('GOOGLE_OAUTH_TOKEN_ENDPOINT')) {
    define('GOOGLE_OAUTH_TOKEN_ENDPOINT', 'https://oauth2.googleapis.com/token');
}

if (!defined('GOOGLE_OAUTH_USERINFO_ENDPOINT')) {
    define('GOOGLE_OAUTH_USERINFO_ENDPOINT', 'https://openidconnect.googleapis.com/v1/userinfo');
}

if (!function_exists('google_oauth_http_request')) {
    function google_oauth_http_request(string $url, string $method = 'GET', array $headers = [], array $body = []): array
    {
        $formattedHeaders = [];
        foreach ($headers as $name => $value) {
            $formattedHeaders[] = $name . ': ' . $value;
        }

        $payload = null;
        if ($method !== 'GET' && !empty($body)) {
            $payload = http_build_query($body, '', '&', PHP_QUERY_RFC3986);
            $formattedHeaders[] = 'Content-Type: application/x-www-form-urlencoded';
        }

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $formattedHeaders,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_FOLLOWLOCATION => true,
            ]);

            $responseBody = curl_exec($ch);
            $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'status' => $statusCode,
                'body' => $responseBody === false ? '' : $responseBody,
                'error' => $error,
            ];
        }

        $contextOptions = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $formattedHeaders),
                'ignore_errors' => true,
                'timeout' => 20,
            ],
        ];

        if ($payload !== null) {
            $contextOptions['http']['content'] = $payload;
        }

        $context = stream_context_create($contextOptions);
        $responseBody = @file_get_contents($url, false, $context);
        $statusCode = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $statusCode = (int) $matches[1];
        }

        return [
            'status' => $statusCode,
            'body' => $responseBody === false ? '' : $responseBody,
            'error' => $responseBody === false ? 'HTTP request failed' : '',
        ];
    }
}

if (!function_exists('google_oauth_sanitize_return_to')) {
    function google_oauth_sanitize_return_to(string $returnTo): string
    {
        $returnTo = trim($returnTo);
        if ($returnTo === '') {
            return '../index.php';
        }

        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $returnTo) || str_starts_with($returnTo, '//')) {
            return '../index.php';
        }

        return $returnTo;
    }
}

if (!function_exists('ensure_google_oauth_columns')) {
    function ensure_google_oauth_columns($conn): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        $requiredColumns = [
            'google_id' => "ALTER TABLE users ADD COLUMN google_id VARCHAR(191) NULL AFTER email_verified_at",
            'oauth_provider' => "ALTER TABLE users ADD COLUMN oauth_provider VARCHAR(20) NULL AFTER google_id",
            'oauth_avatar_url' => "ALTER TABLE users ADD COLUMN oauth_avatar_url VARCHAR(255) NULL AFTER oauth_provider",
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
}

if (!function_exists('google_oauth_auth_url')) {
    function google_oauth_auth_url(string $state): string
    {
        $query = http_build_query([
            'client_id' => GOOGLE_OAUTH_CLIENT_ID,
            'redirect_uri' => google_oauth_redirect_uri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ], '', '&', PHP_QUERY_RFC3986);

        return GOOGLE_OAUTH_AUTH_ENDPOINT . '?' . $query;
    }
}

if (!function_exists('google_oauth_exchange_code')) {
    function google_oauth_exchange_code(string $code): array
    {
        $response = google_oauth_http_request(GOOGLE_OAUTH_TOKEN_ENDPOINT, 'POST', [], [
            'code' => $code,
            'client_id' => GOOGLE_OAUTH_CLIENT_ID,
            'client_secret' => GOOGLE_OAUTH_CLIENT_SECRET,
            'redirect_uri' => google_oauth_redirect_uri(),
            'grant_type' => 'authorization_code',
        ]);

        if ($response['status'] < 200 || $response['status'] >= 300) {
            return [
                'success' => false,
                'error' => 'Unable to exchange the authorization code with Google.',
                'raw' => $response['body'],
            ];
        }

        $data = json_decode($response['body'], true);
        if (!is_array($data) || empty($data['access_token'])) {
            return [
                'success' => false,
                'error' => 'Google did not return an access token.',
                'raw' => $response['body'],
            ];
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }
}

if (!function_exists('google_oauth_fetch_userinfo')) {
    function google_oauth_fetch_userinfo(string $accessToken): array
    {
        $response = google_oauth_http_request(GOOGLE_OAUTH_USERINFO_ENDPOINT, 'GET', [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ]);

        if ($response['status'] < 200 || $response['status'] >= 300) {
            return [
                'success' => false,
                'error' => 'Unable to fetch the Google profile.',
                'raw' => $response['body'],
            ];
        }

        $data = json_decode($response['body'], true);
        if (!is_array($data) || empty($data['sub']) || empty($data['email'])) {
            return [
                'success' => false,
                'error' => 'Google profile response was incomplete.',
                'raw' => $response['body'],
            ];
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }
}
