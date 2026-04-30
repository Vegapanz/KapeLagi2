<?php
include 'config/session.php';
include 'config/db.php';

// Redirect if not logged in
if (!is_logged_in()) {
    header('Location: signin.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get order details - verify user owns this order
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$items_sql = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = [];
while ($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo htmlspecialchars($order['id']); ?> - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/order-details.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>
    
    <div class="order-details-container">
        <!-- Back Button -->
        <div class="details-back-button">
            <a href="orders.php" class="back-link">
                <i class="fas fa-chevron-left"></i> Back to Orders
            </a>
        </div>

        <!-- Order Header -->
        <div class="details-header">
            <div class="details-header-left">
                <h1 class="details-order-id">Order #<?php echo htmlspecialchars($order['id']); ?></h1>
                <p class="details-order-date">
                    <i class="far fa-calendar-alt"></i>
                    Placed on <?php echo date('F d, Y \a\t H:i A', strtotime($order['created_at'])); ?>
                </p>
            </div>
            <div class="details-header-right">
                <div class="details-status-badge">
                    <?php
                        $status = $order['status'];
                        $status_icon = '';
                        $status_text = '';
                        
                        switch($status) {
                            case 'pending':
                                $status_icon = 'utensils';
                                $status_text = 'Preparing';
                                break;
                            case 'processing':
                                $status_icon = 'truck';
                                $status_text = 'For Delivery';
                                break;
                            case 'completed':
                                $status_icon = 'check-circle';
                                $status_text = 'Delivered';
                                break;
                            case 'cancelled':
                                $status_icon = 'times-circle';
                                $status_text = 'Cancelled';
                                break;
                            default:
                                $status_icon = 'clock';
                                $status_text = ucfirst($status);
                        }
                    ?>
                    <i class="fas fa-<?php echo $status_icon; ?>"></i>
                    <span><?php echo $status_text; ?></span>
                </div>
            </div>
        </div>

        <!-- Order Tracker -->
        <div class="details-tracker-section">
            <div class="tracker-title">Order Progress</div>
            <div class="order-tracker">
                <?php
                    $stages = ['pending' => 0, 'processing' => 1, 'completed' => 2];
                    $current_stage = isset($stages[$status]) ? $stages[$status] : 0;
                ?>
                
                <!-- Stage 1: Preparing -->
                <div class="tracker-stage <?php echo ($current_stage >= 0) ? 'active' : ''; ?>">
                    <div class="stage-circle">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="stage-label">
                        <div class="stage-title">Preparing</div>
                        <div class="stage-desc">We're preparing your order</div>
                    </div>
                </div>

                <!-- Stage Divider -->
                <div class="tracker-divider <?php echo ($current_stage >= 1) ? 'active' : ''; ?>"></div>

                <!-- Stage 2: For Delivery -->
                <div class="tracker-stage <?php echo ($current_stage >= 1) ? 'active' : ''; ?>">
                    <div class="stage-circle">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stage-label">
                        <div class="stage-title">For Delivery</div>
                        <div class="stage-desc">On its way to you</div>
                    </div>
                </div>

                <!-- Stage Divider -->
                <div class="tracker-divider <?php echo ($current_stage >= 2) ? 'active' : ''; ?>"></div>

                <!-- Stage 3: Delivered -->
                <div class="tracker-stage <?php echo ($current_stage >= 2) ? 'active' : ''; ?>">
                    <div class="stage-circle">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stage-label">
                        <div class="stage-title">Delivered</div>
                        <div class="stage-desc">Order completed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="details-content">
            <!-- Order Items -->
            <div class="details-items-section">
                <div class="section-title">
                    <i class="fas fa-box"></i> Order Items
                </div>
                <div class="items-list">
                    <?php foreach ($items as $item): ?>
                        <div class="item-card">
                            <div class="item-header">
                                <h4 class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                <div class="item-price-main">₱<?php echo number_format($item['price'], 2); ?></div>
                            </div>
                            <div class="item-meta">
                                <div class="meta-group">
                                    <span class="meta-label">Size:</span>
                                    <span class="meta-value"><?php echo htmlspecialchars($item['size']); ?></span>
                                </div>
                                <div class="meta-group">
                                    <span class="meta-label">Quantity:</span>
                                    <span class="meta-value">x<?php echo $item['quantity']; ?></span>
                                </div>
                                <div class="meta-group">
                                    <span class="meta-label">Subtotal:</span>
                                    <span class="meta-value meta-price">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Sidebar: Order Summary & Delivery Info -->
            <div class="details-sidebar">
                <!-- Order Summary -->
                <div class="summary-card">
                    <div class="summary-title">
                        <i class="fas fa-receipt"></i> Order Summary
                    </div>
                    <div class="summary-details">
                        <div class="summary-row">
                            <span class="summary-label">Subtotal</span>
                            <span class="summary-value">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Shipping</span>
                            <span class="summary-value">Free</span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row summary-total">
                            <span class="summary-label">Total</span>
                            <span class="summary-value">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Delivery Information -->
                <div class="summary-card">
                    <div class="summary-title">
                        <i class="fas fa-map-marker-alt"></i> Delivery Information
                    </div>
                    <div class="delivery-info">
                        <div class="info-group">
                            <div class="info-label">Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer_phone'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Address</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($order['delivery_address'] ?? 'Not provided'); ?>
                                <?php if ($order['city']): ?>
                                    <br><small><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['province']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <?php if ($order['special_notes']): ?>
                    <div class="summary-card">
                        <div class="summary-title">
                            <i class="fas fa-sticky-note"></i> Special Notes
                        </div>
                        <div class="notes-content">
                            <?php echo nl2br(htmlspecialchars($order['special_notes'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="details-footer">
            <a href="orders.php" class="btn btn-footer">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            <a href="menu.php" class="btn btn-footer btn-secondary">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
