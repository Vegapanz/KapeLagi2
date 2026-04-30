<?php
include 'config/session.php';
include 'config/db.php';

// Redirect if not logged in
if (!is_logged_in()) {
    header('Location: signin.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user's orders
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/orders.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <h2 class="mb-4">My Orders</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> You haven't placed any orders yet.
                        <a href="menu.php" class="alert-link">Start shopping</a>
                    </div>
                <?php else: ?>
                    <div class="orders-container">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <!-- Order Header -->
                                <div class="order-header">
                                    <div class="order-header-left">
                                        <h5 class="order-id">Order #<?php echo htmlspecialchars($order['id']); ?></h5>
                                        <p class="order-date">
                                            <i class="far fa-calendar-alt"></i>
                                            <?php echo date('F d, Y', strtotime($order['created_at'])); ?>
                                        </p>
                                    </div>
                                    <div class="order-header-right">
                                        <div class="order-total">
                                            <span class="total-label">Total</span>
                                            <span class="total-amount">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Tracker -->
                                <div class="order-tracker-section">
                                    <div class="tracker-title">Order Status</div>
                                    <div class="order-tracker">
                                        <?php
                                            // Determine current stage
                                            $status = $order['status'];
                                            $stages = ['pending' => 0, 'processing' => 1, 'completed' => 2];
                                            $current_stage = isset($stages[$status]) ? $stages[$status] : 0;
                                        ?>
                                        
                                        <!-- Stage 1: Preparing -->
                                        <div class="tracker-stage <?php echo ($current_stage >= 0) ? 'active' : ''; ?>">
                                            <div class="stage-circle">
                                                <i class="fas fa-<?php echo ($status === 'cancelled') ? 'times' : 'utensils'; ?>"></i>
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
                                                <i class="fas fa-<?php echo ($status === 'cancelled') ? 'times' : 'truck'; ?>"></i>
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
                                                <i class="fas fa-<?php echo ($status === 'cancelled') ? 'times' : 'check-circle'; ?>"></i>
                                            </div>
                                            <div class="stage-label">
                                                <div class="stage-title">Delivered</div>
                                                <div class="stage-desc">Order completed</div>
                                            </div>
                                        </div>

                                        <?php if ($status === 'cancelled'): ?>
                                            <div class="tracker-cancelled">
                                                <i class="fas fa-exclamation-circle"></i> Order Cancelled
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div class="order-items-section">
                                    <button type="button" class="items-toggle" data-order-id="<?php echo $order['id']; ?>">
                                        <span class="items-toggle-text">
                                            <?php
                                                $item_sql = "SELECT COUNT(*) as cnt FROM order_items WHERE order_id = ?";
                                                $item_stmt = $conn->prepare($item_sql);
                                                $item_stmt->bind_param("i", $order['id']);
                                                $item_stmt->execute();
                                                $item_count = $item_stmt->get_result()->fetch_assoc()['cnt'];
                                                echo $item_count . " item" . ($item_count != 1 ? "s" : "");
                                            ?>
                                        </span>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <div class="order-items-list" id="items-<?php echo $order['id']; ?>" style="display: none;">
                                        <?php
                                            $items_sql = "SELECT * FROM order_items WHERE order_id = ?";
                                            $items_stmt = $conn->prepare($items_sql);
                                            $items_stmt->bind_param("i", $order['id']);
                                            $items_stmt->execute();
                                            $items_result = $items_stmt->get_result();
                                            while ($item = $items_result->fetch_assoc()):
                                        ?>
                                            <div class="order-item">
                                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                <div class="item-details">
                                                    <span class="item-size"><?php echo htmlspecialchars($item['size']); ?></span>
                                                    <span class="item-qty">x<?php echo $item['quantity']; ?></span>
                                                    <span class="item-price">₱<?php echo number_format($item['price'], 2); ?></span>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>

                                <!-- Order Footer -->
                                <div class="order-footer">
                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-order-details">
                                        <i class="fas fa-eye"></i> View Full Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle order items visibility
        document.querySelectorAll('.items-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                const itemsList = document.getElementById('items-' + orderId);
                const isHidden = itemsList.style.display === 'none';
                
                itemsList.style.display = isHidden ? 'block' : 'none';
                this.classList.toggle('active', isHidden);
            });
        });
    </script>
</body>
</html>
