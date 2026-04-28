<?php
$page_title = "Order Details";
include 'includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = intval($_GET['id']);

// Get order details
$order = $conn->query("
    SELECT * FROM orders WHERE id = $order_id
")->fetch_assoc();

if (!$order) {
    echo '<div class="alert alert-danger">Order not found</div>';
    include 'includes/footer.php';
    exit;
}

// Get order items
$items = $conn->query("
    SELECT oi.*, p.name as product_name
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");

$status_class = 'pending';
if ($order['status'] === 'completed') $status_class = 'completed';
if ($order['status'] === 'processing') $status_class = 'processing';
if ($order['status'] === 'cancelled') $status_class = 'cancelled';
?>

<h1 class="page-title">Order #<?php echo $order_id; ?></h1>

<div class="row mb-4">
    <div class="col-lg-8">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Order Items</h3>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($item = $items->fetch_assoc()) {
                        $total = $item['price'] * $item['quantity'];
                        echo '<tr>
                                <td>' . htmlspecialchars($item['product_name']) . '</td>
                                <td>' . htmlspecialchars($item['size']) . '</td>
                                <td>₱' . number_format($item['price'], 2) . '</td>
                                <td>' . $item['quantity'] . '</td>
                                <td>₱' . number_format($total, 2) . '</td>
                              </tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Order Summary</h3>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="color: #999; font-size: 0.9rem;">Customer Name</label>
                <p><?php echo htmlspecialchars($order['customer_name']); ?></p>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="color: #999; font-size: 0.9rem;">Customer Email</label>
                <p><?php echo htmlspecialchars($order['customer_email']); ?></p>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="color: #999; font-size: 0.9rem;">Delivery Address</label>
                <p><?php echo htmlspecialchars($order['delivery_address'] ?? 'Not provided'); ?></p>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="color: #999; font-size: 0.9rem;">Status</label>
                <p>
                    <select id="orderStatus" class="form-select" style="max-width: 200px; border: 1px solid #e0d9cd; border-radius: 5px; padding: 8px;" onchange="updateOrderStatus(<?php echo $order_id; ?>, this.value)">
                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </p>
            </div>

            <div style="border-top: 2px solid #e0d9cd; padding-top: 15px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Subtotal</span>
                    <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1rem;">
                    <span>Total</span>
                    <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <div>
                <label style="color: #999; font-size: 0.9rem;">Date</label>
                <p><?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
// Update Order Status
function updateOrderStatus(orderId, status) {
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', status);

    fetch('api.php?action=update-order-status', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const statusSelect = document.getElementById('orderStatus');
            statusSelect.style.backgroundColor = '#c8e6c9';
            setTimeout(() => {
                statusSelect.style.backgroundColor = '';
            }, 1000);
        } else {
            alert('Error: ' + (data.error || 'Failed to update status'));
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order status');
        location.reload();
    });
}
</script>

<?php include 'includes/footer.php'; ?>
