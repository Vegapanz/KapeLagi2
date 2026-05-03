<?php
$page_title = "Analytics";
include 'includes/header.php';

$range_days = intval($_GET['range'] ?? 30);
$allowed_ranges = [7, 30, 90, 365];
if (!in_array($range_days, $allowed_ranges, true)) {
    $range_days = 30;
}

$range_start = date('Y-m-d', strtotime('-' . ($range_days - 1) . ' days'));

$kpi_query = $conn->query("
    SELECT
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(CASE WHEN status <> 'cancelled' THEN total_amount ELSE 0 END) as revenue,
        SUM(CASE WHEN status <> 'cancelled' THEN 1 ELSE 0 END) as valid_orders
    FROM orders
    WHERE DATE(created_at) >= '{$range_start}'
");
$kpis = $kpi_query ? $kpi_query->fetch_assoc() : null;

$total_orders = (int) ($kpis['total_orders'] ?? 0);
$cancelled_orders = (int) ($kpis['cancelled_orders'] ?? 0);
$valid_orders = (int) ($kpis['valid_orders'] ?? 0);
$revenue = (float) ($kpis['revenue'] ?? 0);
$avg_order_value = $valid_orders > 0 ? ($revenue / $valid_orders) : 0;
$cancel_rate = $total_orders > 0 ? (($cancelled_orders / $total_orders) * 100) : 0;

$daily_query = $conn->query("
    SELECT
        DATE(created_at) as day,
        SUM(CASE WHEN status <> 'cancelled' THEN total_amount ELSE 0 END) as revenue,
        COUNT(*) as orders
    FROM orders
    WHERE DATE(created_at) >= '{$range_start}'
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
");

$daily_map = [];
if ($daily_query) {
    while ($row = $daily_query->fetch_assoc()) {
        $daily_map[$row['day']] = [
            'revenue' => (float) ($row['revenue'] ?? 0),
            'orders' => (int) ($row['orders'] ?? 0)
        ];
    }
}

$daily_labels = [];
$daily_revenue = [];
$daily_orders = [];
for ($i = 0; $i < $range_days; $i++) {
    $date_key = date('Y-m-d', strtotime($range_start . ' +' . $i . ' days'));
    $daily_labels[] = date('M d', strtotime($date_key));
    $daily_revenue[] = $daily_map[$date_key]['revenue'] ?? 0;
    $daily_orders[] = $daily_map[$date_key]['orders'] ?? 0;
}

$status_query = $conn->query("
    SELECT status, COUNT(*) as count
    FROM orders
    WHERE DATE(created_at) >= '{$range_start}'
    GROUP BY status
");

$status_labels = [];
$status_counts = [];
$status_colors = [];
$status_color_map = [
    'completed' => '#2E7D32',
    'pending' => '#F9A825',
    'processing' => '#1565C0',
    'cancelled' => '#C62828'
];
if ($status_query) {
    while ($row = $status_query->fetch_assoc()) {
        $status_key = strtolower((string) ($row['status'] ?? ''));
        $status_labels[] = ucfirst($status_key);
        $status_counts[] = (int) ($row['count'] ?? 0);
        $status_colors[] = $status_color_map[$status_key] ?? '#757575';
    }
}

$top_products_query = $conn->query("
    SELECT
        COALESCE(p.name, oi.product_name, 'Unknown Item') as product_name,
        SUM(oi.quantity) as quantity_sold,
        SUM(oi.price * oi.quantity) as product_revenue
    FROM order_items oi
    INNER JOIN orders o ON o.id = oi.order_id
    LEFT JOIN products p ON p.id = oi.product_id
    WHERE DATE(o.created_at) >= '{$range_start}'
    GROUP BY COALESCE(p.name, oi.product_name, 'Unknown Item')
    ORDER BY quantity_sold DESC
    LIMIT 8
");

$top_product_labels = [];
$top_product_quantities = [];
$top_product_revenue = [];
if ($top_products_query) {
    while ($row = $top_products_query->fetch_assoc()) {
        $top_product_labels[] = $row['product_name'];
        $top_product_quantities[] = (int) ($row['quantity_sold'] ?? 0);
        $top_product_revenue[] = (float) ($row['product_revenue'] ?? 0);
    }
}

$hour_query = $conn->query("
    SELECT HOUR(created_at) as order_hour, COUNT(*) as total
    FROM orders
    WHERE DATE(created_at) >= '{$range_start}'
    GROUP BY HOUR(created_at)
    ORDER BY HOUR(created_at)
");

$hour_map = [];
if ($hour_query) {
    while ($row = $hour_query->fetch_assoc()) {
        $hour_map[(int) $row['order_hour']] = (int) ($row['total'] ?? 0);
    }
}

$hour_labels = [];
$hour_totals = [];
for ($h = 0; $h < 24; $h++) {
    $hour_labels[] = str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00';
    $hour_totals[] = $hour_map[$h] ?? 0;
}
?>

<h1 class="page-title">Analytics</h1>

<div class="section-card" style="margin-bottom: 20px;">
    <form method="get" class="d-flex align-items-end" style="gap: 12px; flex-wrap: wrap;">
        <div>
            <label class="form-label" for="range" style="margin-bottom: 6px;">Time Range</label>
            <select id="range" name="range" class="form-select" style="min-width: 180px;">
                <option value="7" <?php echo $range_days === 7 ? 'selected' : ''; ?>>Last 7 days</option>
                <option value="30" <?php echo $range_days === 30 ? 'selected' : ''; ?>>Last 30 days</option>
                <option value="90" <?php echo $range_days === 90 ? 'selected' : ''; ?>>Last 90 days</option>
                <option value="365" <?php echo $range_days === 365 ? 'selected' : ''; ?>>Last 365 days</option>
            </select>
        </div>
        <button type="submit" class="btn" style="background-color: #1A0F0A; color: #E8E0D0; border: none; padding: 10px 16px;">Apply</button>
    </form>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-peso-sign"></i></div>
            <div class="kpi-label">Revenue</div>
            <div class="kpi-value">₱<?php echo number_format($revenue, 2); ?></div>
            <div class="kpi-trend positive">Last <?php echo $range_days; ?> days</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="kpi-label">Orders</div>
            <div class="kpi-value"><?php echo number_format($total_orders); ?></div>
            <div class="kpi-trend positive">Including cancelled</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-receipt"></i></div>
            <div class="kpi-label">Avg Order Value</div>
            <div class="kpi-value">₱<?php echo number_format($avg_order_value, 2); ?></div>
            <div class="kpi-trend positive">Excluding cancelled</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-ban"></i></div>
            <div class="kpi-label">Cancellation Rate</div>
            <div class="kpi-value"><?php echo number_format($cancel_rate, 1); ?>%</div>
            <div class="kpi-trend negative"><?php echo $cancelled_orders; ?> cancelled</div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-8">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Sales Trend</h3>
            </div>
            <div class="chart-container">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Order Status Mix</h3>
            </div>
            <div class="chart-container" style="height: 260px;">
                <canvas id="statusMixChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Top Products</h3>
            </div>
            <div class="chart-container" style="height: 320px;">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Peak Hours</h3>
            </div>
            <div class="chart-container" style="height: 320px;">
                <canvas id="hourlyOrdersChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
const pesoFormatter = (value) => 'P' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
new Chart(salesTrendCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($daily_labels); ?>,
        datasets: [
            {
                label: 'Revenue',
                data: <?php echo json_encode($daily_revenue); ?>,
                borderColor: '#8b6f47',
                backgroundColor: 'rgba(196, 168, 112, 0.12)',
                yAxisID: 'yRevenue',
                borderWidth: 2,
                tension: 0.35,
                fill: true
            },
            {
                label: 'Orders',
                data: <?php echo json_encode($daily_orders); ?>,
                borderColor: '#1976d2',
                backgroundColor: 'rgba(25, 118, 210, 0.12)',
                yAxisID: 'yOrders',
                borderWidth: 2,
                tension: 0.35,
                fill: false
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
            yRevenue: {
                type: 'linear',
                position: 'left',
                ticks: {
                    callback: (value) => 'P' + Number(value).toLocaleString()
                }
            },
            yOrders: {
                type: 'linear',
                position: 'right',
                grid: { drawOnChartArea: false },
                beginAtZero: true
            }
        }
    }
});

const statusMixCtx = document.getElementById('statusMixChart').getContext('2d');
new Chart(statusMixCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($status_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($status_counts); ?>,
            backgroundColor: <?php echo json_encode($status_colors); ?>,
            borderColor: '#f5f1e8',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($top_product_labels); ?>,
        datasets: [{
            label: 'Units Sold',
            data: <?php echo json_encode($top_product_quantities); ?>,
            backgroundColor: '#c4a870',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

const hourlyOrdersCtx = document.getElementById('hourlyOrdersChart').getContext('2d');
new Chart(hourlyOrdersCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($hour_labels); ?>,
        datasets: [{
            label: 'Orders',
            data: <?php echo json_encode($hour_totals); ?>,
            backgroundColor: '#6d4c41',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: {
                ticks: {
                    maxTicksLimit: 8
                }
            },
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
