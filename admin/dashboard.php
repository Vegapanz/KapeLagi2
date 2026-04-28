<?php
$page_title = "Dashboard";
include 'includes/header.php';

// Get time period filter
$period = isset($_GET['period']) ? $_GET['period'] : 'month'; // 'month' or 'year'

// Get statistics from database
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders")->fetch_assoc()['total'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_customers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending', 'processing')")->fetch_assoc()['count'];

// Get revenue data based on period
if ($period === 'month') {
    // Last 6 months
    $monthly_data = $conn->query("
        SELECT DATE_FORMAT(created_at, '%b') as month, SUM(total_amount) as revenue 
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY created_at
    ");
} else {
    // Last 12 months
    $monthly_data = $conn->query("
        SELECT DATE_FORMAT(created_at, '%b %Y') as month, SUM(total_amount) as revenue 
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY created_at
    ");
}

$months = [];
$revenues = [];
while ($row = $monthly_data->fetch_assoc()) {
    $months[] = $row['month'];
    $revenues[] = (float)$row['revenue'];
}

// Get category revenue
$category_data = $conn->query("
    SELECT p.category, SUM(oi.price * oi.quantity) as category_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.category
    ORDER BY category_revenue DESC
");

$categories = [];
$category_revenues = [];
$colors = ['#6d4c41', '#a1887f', '#d7ccc8', '#c4a870'];
$color_index = 0;

while ($row = $category_data->fetch_assoc()) {
    $categories[] = $row['category'];
    $category_revenues[] = (float)$row['category_revenue'];
}

// Get recent orders
$recent_orders = $conn->query("
    SELECT o.id, o.customer_name, GROUP_CONCAT(p.name SEPARATOR ', ') as items, 
           o.total_amount, o.status, o.created_at
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
");

// Get top selling products
$top_sellers = $conn->query("
    SELECT p.name, SUM(oi.quantity) as total_sales, SUM(oi.price * oi.quantity) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_sales DESC
    LIMIT 5
");
?>

<h1 class="page-title">Overview</h1>

<!-- KPI Cards Row 1 -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-peso-sign"></i></div>
            <div class="kpi-label">Total Revenue</div>
            <div class="kpi-value">₱<?php echo number_format($total_revenue, 2); ?></div>
            <div class="kpi-trend positive">
                <i class="fas fa-arrow-up"></i> 12.5%
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="kpi-label">Total Orders</div>
            <div class="kpi-value"><?php echo $total_orders; ?></div>
            <div class="kpi-trend positive">
                <i class="fas fa-arrow-up"></i> 8.2%
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-users"></i></div>
            <div class="kpi-label">Customers Today</div>
            <div class="kpi-value"><?php echo $total_customers; ?></div>
            <div class="kpi-trend negative">
                <i class="fas fa-arrow-down"></i> 3.1%
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="kpi-label">Bugs Stat</div>
            <div class="kpi-value"><?php echo $pending_orders; ?></div>
            <div class="kpi-trend positive">
                <i class="fas fa-arrow-up"></i> 15.3%
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Revenue Overview</h3>
                <div>
                    <a href="?period=month" class="btn btn-sm" style="background-color: <?php echo $period === 'month' ? '#c4a870' : '#f0ebe4'; ?>; color: <?php echo $period === 'month' ? 'white' : '#333'; ?>; border: none; margin-right: 5px; text-decoration: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Month</a>
                    <a href="?period=year" class="btn btn-sm" style="background-color: <?php echo $period === 'year' ? '#c4a870' : '#f0ebe4'; ?>; color: <?php echo $period === 'year' ? 'white' : '#333'; ?>; border: none; text-decoration: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Year</a>
                </div>
            </div>
            <p style="color: #999; font-size: 0.9rem; margin-bottom: 20px;"><?php echo $period === 'month' ? '6-month revenue and order trends' : '12-month revenue and order trends'; ?></p>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Categories</h3>
            </div>
            <div class="chart-container" style="height: 280px;">
                <canvas id="categoriesChart"></canvas>
            </div>
            <div style="margin-top: 20px; font-size: 0.9rem;">
                <?php 
                $top_sellers_query = $conn->query("
                    SELECT p.category, SUM(oi.price * oi.quantity) as revenue
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    GROUP BY p.category
                    ORDER BY revenue DESC
                    LIMIT 4
                ");
                while ($row = $top_sellers_query->fetch_assoc()) {
                    echo '<div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e0d9cd;">
                            <span>' . htmlspecialchars($row['category']) . '</span>
                            <span style="font-weight: bold;">₱' . number_format($row['revenue'], 0) . '</span>
                          </div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders and Top Sellers Row -->
<div class="row">
    <div class="col-lg-7">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Recent Orders</h3>
                <a href="orders.php">View All →</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($recent_orders->num_rows > 0) {
                        while ($order = $recent_orders->fetch_assoc()) {
                            $status_class = 'pending';
                            if ($order['status'] === 'completed') $status_class = 'completed';
                            if ($order['status'] === 'processing') $status_class = 'processing';
                            if ($order['status'] === 'cancelled') $status_class = 'cancelled';
                            
                            echo '<tr>
                                    <td>#' . htmlspecialchars($order['id']) . '</td>
                                    <td>' . htmlspecialchars($order['customer_name']) . '</td>
                                    <td>' . htmlspecialchars(substr($order['items'] ?? '', 0, 30)) . '...</td>
                                    <td>₱' . number_format($order['total_amount'], 2) . '</td>
                                    <td><span class="status-badge ' . $status_class . '">' . ucfirst($order['status']) . '</span></td>
                                    <td><a href="orders.php?id=' . $order['id'] . '" style="color: #c4a870; cursor: pointer;"><i class="fas fa-edit"></i></a></td>
                                  </tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6" style="text-align: center; padding: 20px;">No orders yet</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Top Sellers</h3>
            </div>
            <div>
                <?php
                if ($top_sellers->num_rows > 0) {
                    while ($seller = $top_sellers->fetch_assoc()) {
                        echo '<div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #e0d9cd;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 40px; height: 40px; background-color: #c4a870; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                                        <i class="fas fa-cup"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: bold;">' . htmlspecialchars($seller['name']) . '</div>
                                        <div style="font-size: 0.85rem; color: #999;">' . htmlspecialchars($seller['total_sales']) . ' sold</div>
                                    </div>
                                </div>
                                <div style="font-weight: bold; color: #c4a870;">₱' . number_format($seller['revenue'], 0) . '</div>
                              </div>';
                    }
                } else {
                    echo '<p style="color: #999; text-align: center; padding: 20px;">No sales data yet</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode($revenues); ?>,
            borderColor: '#8b6f47',
            backgroundColor: 'rgba(196, 168, 112, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Categories Chart
const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
new Chart(categoriesCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($categories); ?>,
        datasets: [{
            data: <?php echo json_encode($category_revenues); ?>,
            backgroundColor: ['#6d4c41', '#a1887f', '#d7ccc8', '#c4a870']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
