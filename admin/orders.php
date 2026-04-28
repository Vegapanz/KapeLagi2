<?php
$page_title = "Orders";
include 'includes/header.php';

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
$query = "
    SELECT o.id, o.customer_name, o.customer_email, GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as items,
           o.total_amount, o.status, o.created_at, o.delivery_address
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
";

if ($status_filter !== 'all') {
    $query .= " WHERE o.status = '" . $conn->real_escape_string($status_filter) . "'";
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC";

$orders = $conn->query($query);
$total_orders = $orders->num_rows;

// Get status counts
$pending = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$processing = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'")->fetch_assoc()['count'];
$completed = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'")->fetch_assoc()['count'];
$cancelled = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled'")->fetch_assoc()['count'];
?>

<h1 class="page-title">All Orders</h1>

<!-- Filter Dropdown -->
<div style="margin-bottom: 20px;">
    <select class="form-select" style="max-width: 200px;" onchange="location = 'orders.php?status=' + this.value;">
        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status (<?php echo $pending + $processing + $completed + $cancelled; ?>)</option>
        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed (<?php echo $completed; ?>)</option>
        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing (<?php echo $processing; ?>)</option>
        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending (<?php echo $pending; ?>)</option>
        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled (<?php echo $cancelled; ?>)</option>
    </select>
</div>

<div class="section-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($orders->num_rows > 0) {
                while ($order = $orders->fetch_assoc()) {
                    $status_class = 'pending';
                    if ($order['status'] === 'completed') $status_class = 'completed';
                    if ($order['status'] === 'processing') $status_class = 'processing';
                    if ($order['status'] === 'cancelled') $status_class = 'cancelled';
                    
                    echo '<tr>
                            <td><strong>#' . htmlspecialchars($order['id']) . '</strong></td>
                            <td>' . htmlspecialchars($order['customer_name']) . '</td>
                            <td><small>' . htmlspecialchars(substr($order['items'] ?? '', 0, 40)) . '</small></td>
                            <td>₱' . number_format($order['total_amount'], 2) . '</td>
                            <td>
                                <select class="form-select form-select-sm" style="max-width: 150px; border: 1px solid #e0d9cd; border-radius: 5px; padding: 5px; cursor: pointer;" onchange="updateOrderStatus(' . $order['id'] . ', this.value)">
                                    <option value="pending" ' . ($order['status'] === 'pending' ? 'selected' : '') . '>Pending</option>
                                    <option value="processing" ' . ($order['status'] === 'processing' ? 'selected' : '') . '>Processing</option>
                                    <option value="completed" ' . ($order['status'] === 'completed' ? 'selected' : '') . '>Completed</option>
                                    <option value="cancelled" ' . ($order['status'] === 'cancelled' ? 'selected' : '') . '>Cancelled</option>
                                </select>
                            </td>
                            <td><small>' . date('M d, Y H:i', strtotime($order['created_at'])) . '</small></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="order-details.php?id=' . $order['id'] . '" class="btn-action btn-edit" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                          </tr>';
                }
            } else {
                echo '<tr><td colspan="7" style="text-align: center; padding: 40px;">No orders found</td></tr>';
            }
            ?>
        </tbody>
    </table>
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
            // Flash effect to show change
            const row = event.target.closest('tr');
            row.style.backgroundColor = '#ffffcc';
            setTimeout(() => {
                row.style.backgroundColor = '';
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
