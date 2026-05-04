<?php
header('Content-Type: application/json');
include '../config/session.php';
include '../config/db.php';

// Only logged in users can cancel their orders
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'cancel_order') {
    ensure_order_cancellation_reason_column($conn);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $note = isset($_POST['cancellation_note']) ? trim($_POST['cancellation_note']) : '';
    $user_id = get_user_id();

    if ($order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order']);
        exit;
    }

    // Verify ownership and current status
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $order = $res ? $res->fetch_assoc() : null;

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    if ($order['status'] === 'completed' || $order['status'] === 'cancelled') {
        echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled']);
        exit;
    }

    if ($note === '') {
        echo json_encode(['success' => false, 'message' => 'Cancellation note required']);
        exit;
    }

    $update = $conn->prepare("UPDATE orders SET status = 'cancelled', cancellation_reason = ? WHERE id = ? AND user_id = ?");
    $update->bind_param('sii', $note, $order_id, $user_id);

    if ($update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order cancelled']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
    }

    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
