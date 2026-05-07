<?php
include 'config/session.php';
include 'config/db.php';
include 'config/paymongo.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: signin.php');
    exit;
}

$user_id = get_user_id();
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$message = '';
$order_status = '';

if ($order_id > 0) {
    $stmt = $conn->prepare("SELECT id, status, payment_method, total_amount, payment_intent_id FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order) {
        $order_status = $order['status'];
        $sourceId = $order['payment_intent_id'];
            $source_status = null;

            if ($sourceId) {
                $source = getPaymongoSource($sourceId);
                if ($source && isset($source['data']['attributes']['status'])) {
                    $source_status = $source['data']['attributes']['status'];

                    if (in_array($source_status, ['paid', 'authorized', 'chargeable'], true) && $order_status !== 'processing') {
                        $update_stmt = $conn->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
                        $update_stmt->bind_param("i", $order_id);
                        $update_stmt->execute();
                        $order_status = 'processing';
                    } elseif (in_array($source_status, ['failed', 'cancelled'], true) && $order_status !== 'cancelled') {
                    $order_status = 'cancelled';
                }
            }
        }

        if ($order['payment_method'] === 'GCASH') {
            if ($order_status === 'processing') {
                $message = 'Payment successful! Your order is now being prepared.';
            } elseif ($order_status === 'pending') {
                $message = 'Payment is being processed. Please wait for confirmation.';
            } elseif ($order_status === 'cancelled') {
                $message = 'Payment was cancelled or failed. Please try again.';
            } else {
                $message = 'Payment completed. Your order is being prepared.';
            }
        }
    } else {
        $message = 'Order not found.';
    }
} else {
    $message = 'Invalid payment reference.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body.payment-status-page { background: #1a0f0a; color: #fff; min-height: 100vh; }
        .payment-status-wrapper { display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 80px); padding: 60px 20px; }
        .payment-status-card { width: 100%; max-width: 760px; background: #ffffff; border-radius: 28px; overflow: hidden; box-shadow: 0 28px 70px rgba(0,0,0,0.28); }
        .payment-status-card-inner { padding: 60px 50px; text-align: center; }
        .payment-status-icon { width: 96px; height: 96px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2.5rem; margin-bottom: 28px; }
        .payment-status-icon.success { background: #1c7c4f; color: #fff; }
        .payment-status-icon.warning { background: #f6c555; color: #1a0f0a; }
        .payment-status-icon.failed { background: #d92e3c; color: #fff; }
        .payment-status-title { font-size: 2.7rem; margin-bottom: 14px; letter-spacing: -0.04em; font-family: 'Anton', sans-serif; }
        .payment-status-text { color: #4f4f4f; font-size: 1rem; line-height: 1.8; max-width: 650px; margin: 0 auto 34px; }
        .payment-status-footer { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; margin-top: 18px; }
        .payment-status-footer .btn { min-width: 150px; padding: 12px 20px; border-radius: 10px; }
        .payment-status-footer .btn-outline-primary { color: #0d6efd; border-color: #0d6efd; background: transparent; }
        .payment-status-footer .btn-outline-primary:hover { background: rgba(13,110,253,.08); }
        .payment-status-meta { color: #8c8c8c; margin-top: 8px; font-size: 0.95rem; }
        @media (max-width: 576px) {
            .payment-status-card-inner { padding: 42px 22px; }
            .payment-status-title { font-size: 2rem; }
        }
    </style>
</head>

<body class="payment-status-page">
    <?php include 'components/navbar.php'; ?>

    <section class="payment-status-wrapper">
        <div class="payment-status-card">
            <div class="payment-status-card-inner">
                <?php if ($order_status === 'processing'): ?>
                    <div class="payment-status-icon success">
                        <i class="fas fa-check"></i>
                    </div>
                    <h1 class="payment-status-title">Payment Successful!</h1>
                <?php elseif ($order_status === 'pending'): ?>
                    <div class="payment-status-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h1 class="payment-status-title">Payment Processing</h1>
                <?php else: ?>
                    <div class="payment-status-icon failed">
                        <i class="fas fa-times"></i>
                    </div>
                    <h1 class="payment-status-title">Payment Failed</h1>
                <?php endif; ?>

                <p class="payment-status-text"><?php echo htmlspecialchars($message); ?></p>

                <?php if ($order_id > 0): ?>
                    <p class="payment-status-meta">Order ID: #<?php echo $order_id; ?></p>
                <?php endif; ?>

                <div class="payment-status-footer">
                    <a href="orders.php" class="btn btn-primary">View Orders</a>
                    <a href="menu.php" class="btn btn-outline-primary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>