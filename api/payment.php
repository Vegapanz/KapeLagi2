<?php
header('Content-Type: application/json');
include '../config/session.php';
include '../config/db.php';
include '../config/paymongo.php';

// Only logged in users can process payments
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'create_gcash_payment') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;

    if ($order_id <= 0 || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID or amount']);
        exit;
    }

    $user_id = get_user_id();

    // Verify order ownership and status
    $stmt = $conn->prepare("SELECT status, payment_method FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $order = $res ? $res->fetch_assoc() : null;

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    if ($order['status'] !== 'pending' || $order['payment_method'] !== 'GCASH') {
        echo json_encode(['success' => false, 'message' => 'Order is not eligible for GCash payment']);
        exit;
    }

    // Create GCash source for redirect-based payment
    $description = "KapeLagi Order #" . $order_id;
    $source = createGCashSource($amount, $description, $order_id);

    if (!$source || !isset($source['data']['id'])) {
        echo json_encode(['success' => false, 'message' => 'Failed to create GCash source']);
        exit;
    }

    $sourceId = $source['data']['id'];
    $checkoutUrl = $source['data']['attributes']['redirect']['checkout_url'] ?? null;
    if (!$checkoutUrl) {
        echo json_encode(['success' => false, 'message' => 'PayMongo did not provide a checkout redirect URL']);
        exit;
    }

    // Store payment source ID in orders table
    $update_stmt = $conn->prepare("UPDATE orders SET payment_intent_id = ? WHERE id = ?");
    $update_stmt->bind_param("si", $sourceId, $order_id);
    $update_stmt->execute();

    echo json_encode([
        'success' => true,
        'payment_intent_id' => $sourceId,
        'checkout_url' => $checkoutUrl
    ]);

} elseif ($action === 'check_payment_status') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

    if ($order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        exit;
    }

    $user_id = get_user_id();

    // Get order with payment intent ID
    $stmt = $conn->prepare("SELECT payment_intent_id, status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $order = $res ? $res->fetch_assoc() : null;

    if (!$order || !$order['payment_intent_id']) {
        echo json_encode(['success' => false, 'message' => 'Order or payment source not found']);
        exit;
    }

    // Get payment source status
    $source = getPaymongoSource($order['payment_intent_id']);

    if (!$source || !isset($source['data']['attributes']['status'])) {
        echo json_encode(['success' => false, 'message' => 'Failed to retrieve payment source status']);
        exit;
    }

    $status = $source['data']['attributes']['status'];

    // Map PayMongo source status to order status values used by the app
    $new_status = $order['status'];
    if (in_array($status, ['paid', 'authorized', 'chargeable'], true)) {
        $new_status = 'processing';
    } elseif (in_array($status, ['failed', 'cancelled'], true)) {
        $new_status = 'cancelled';
    } elseif ($status === 'pending') {
        $new_status = 'pending';
    }

    if ($new_status !== $order['status']) {
        $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $order_id);
        $update_stmt->execute();
    }

    echo json_encode([
        'success' => true,
        'status' => $status,
        'order_status' => $new_status
    ]);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>