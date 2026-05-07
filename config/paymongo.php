<?php
// PayMongo Configuration
define('PAYMONGO_PUBLIC_KEY', 'pk_test_HExqToaPkL7995G8YwikuShZ');
define('PAYMONGO_SECRET_KEY', 'sk_test_SBfNUaNAkoVr5HA8uo1VKWm5');
define('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1');

// PayMongo API endpoints
define('PAYMONGO_SOURCES_URL', PAYMONGO_BASE_URL . '/sources');
define('PAYMONGO_PAYMENTS_URL', PAYMONGO_BASE_URL . '/payments');
define('PAYMONGO_PAYMENT_INTENTS_URL', PAYMONGO_BASE_URL . '/payment_intents');

// Success and failure URLs for GCash payments
define('PAYMONGO_SUCCESS_URL', 'http://localhost/KapeLagi/payment-success.php?order_id=%ORDER_ID%');
define('PAYMONGO_FAILED_URL', 'http://localhost/KapeLagi/checkout.php');

function paymongoSuccessUrl($orderId = null) {
    return str_replace('%ORDER_ID%', urlencode($orderId), PAYMONGO_SUCCESS_URL);
}

// Function to make API requests to PayMongo
function paymongoApiRequest($endpoint, $method = 'POST', $data = null) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    if ($data && ($method === 'POST' || $method === 'PUT')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    } else {
        error_log('PayMongo API Error: ' . $response);
        return false;
    }
}

// Create GCash payment intent
function createGCashPaymentIntent($amount, $description = 'KapeLagi Order Payment', $orderId = null) {
    $data = [
        'data' => [
            'attributes' => [
                'amount' => $amount * 100, // Convert to centavos
                'currency' => 'PHP',
                'payment_method_allowed' => ['gcash'],
                'description' => $description
            ]
        ]
    ];

    return paymongoApiRequest(PAYMONGO_PAYMENT_INTENTS_URL, 'POST', $data);
}

// Create GCash source for redirect-based payment
function createGCashSource($amount, $description = 'KapeLagi Order Payment', $orderId = null) {
    $redirect = [
        'success' => paymongoSuccessUrl($orderId),
        'failed' => PAYMONGO_FAILED_URL
    ];

    $data = [
        'data' => [
            'attributes' => [
                'amount' => $amount * 100,
                'currency' => 'PHP',
                'type' => 'gcash',
                'redirect' => $redirect,
                'billing' => [
                    'name' => 'KapeLagi Customer',
                    'email' => 'customer@kapelagi.com'
                ],
                'description' => $description
            ]
        ]
    ];

    return paymongoApiRequest(PAYMONGO_SOURCES_URL, 'POST', $data);
}

function getPaymongoSource($sourceId) {
    return paymongoApiRequest(PAYMONGO_SOURCES_URL . '/' . $sourceId, 'GET');
}

// Attach payment method to payment intent
function attachGCashToPaymentIntent($paymentIntentId, $paymentMethodId) {
    $data = [
        'data' => [
            'attributes' => [
                'payment_method' => $paymentMethodId,
                'return_url' => PAYMONGO_SUCCESS_URL
            ]
        ]
    ];

    return paymongoApiRequest(PAYMONGO_PAYMENT_INTENTS_URL . '/' . $paymentIntentId . '/attach', 'POST', $data);
}

// Get payment intent status
function getPaymentIntent($paymentIntentId) {
    return paymongoApiRequest(PAYMONGO_PAYMENT_INTENTS_URL . '/' . $paymentIntentId, 'GET');
}
?>