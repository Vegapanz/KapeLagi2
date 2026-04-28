<?php
$page_title = "Customers";
include 'includes/header.php';

// Get all customers with their stats
$query = "
    SELECT u.id, u.name, u.email, u.phone,
           COUNT(DISTINCT o.id) as total_orders,
           COALESCE(SUM(o.total_amount), 0) as total_spent,
           COALESCE(SUM(oi.quantity), 0) as points
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE u.role = 'customer'
    GROUP BY u.id
    ORDER BY total_spent DESC
";

$customers = $conn->query($query);
$total_customers = $customers->num_rows;
?>

<h1 class="page-title">Customers</h1>
<p style="color: #999; margin-bottom: 30px;">Total: <strong><?php echo $total_customers; ?> customers</strong></p>

<div class="row">
    <?php
    if ($customers->num_rows > 0) {
        while ($customer = $customers->fetch_assoc()) {
            $initials = substr($customer['name'], 0, 1);
            $status = $customer['total_orders'] > 0 ? 'active' : 'inactive';
            ?>
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="customer-card">
                    <div class="customer-avatar">
                        <?php echo strtoupper($initials); ?>
                    </div>
                    <div class="customer-name"><?php echo htmlspecialchars($customer['name']); ?></div>
                    <div class="customer-email"><?php echo htmlspecialchars($customer['email']); ?></div>
                    <?php if ($customer['phone']) { ?>
                        <div class="customer-email"><?php echo htmlspecialchars($customer['phone']); ?></div>
                    <?php } ?>
                    <div class="customer-stat">
                        <div class="customer-stat-item">
                            <div class="customer-stat-label">Orders</div>
                            <div class="customer-stat-value"><?php echo $customer['total_orders']; ?></div>
                        </div>
                        <div class="customer-stat-item">
                            <div class="customer-stat-label">Total Spent</div>
                            <div class="customer-stat-value">₱<?php echo number_format($customer['total_spent'], 0); ?></div>
                        </div>
                        <div class="customer-stat-item">
                            <div class="customer-stat-label">Points</div>
                            <div class="customer-stat-value"><?php echo $customer['points']; ?></div>
                        </div>
                    </div>
                    <div style="margin-top: 15px; display: flex; gap: 8px; justify-content: center;">
                        <span class="status-badge <?php echo $status; ?>">
                            <i class="fas fa-<?php echo $status === 'active' ? 'check-circle' : 'times-circle'; ?>"></i>
                            <?php echo ucfirst($status); ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="col-12"><p style="text-align: center; color: #999; padding: 40px;">No customers yet</p></div>';
    }
    ?>
</div>

<?php include 'includes/footer.php'; ?>
