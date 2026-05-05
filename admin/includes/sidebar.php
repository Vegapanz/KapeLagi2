<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Navigation -->
<nav class="admin-sidebar">
    <div class="sidebar-header">
        <h3 class="text-center mb-4">
            <i class="fas fa-coffee"></i> KapeLagi Admin
        </h3>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-chart-line"></i> Overview
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                <i class="fas fa-users"></i> Customers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'menu-items.php' ? 'active' : ''; ?>" href="menu-items.php">
                <i class="fas fa-bars"></i> Menu Items
            </a>
        </li>
        <?php
        // low stock badge
        $low_stock_count = 0;
        if (isset($conn)) {
            $r = $conn->query("SELECT COUNT(*) AS cnt FROM ingredients WHERE stock <= low_stock_threshold");
            if ($r) {
                $low_stock_count = (int) ($r->fetch_assoc()['cnt'] ?? 0);
            }
        }
        ?>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'inventory.php' ? 'active' : ''; ?>" href="inventory.php">
                <i class="fas fa-warehouse"></i> Inventory
                <?php if ($low_stock_count > 0): ?>
                    <span class="badge bg-danger" style="margin-left:8px;"><?php echo $low_stock_count; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'product-ingredients.php' ? 'active' : ''; ?>" href="product-ingredients.php">
                <i class="fas fa-project-diagram"></i> Product Ingredients
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'analytics.php' ? 'active' : ''; ?>" href="analytics.php">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>
    </ul>

    <div class="sidebar-footer mt-auto">
        <hr>
        <div class="user-info mb-3">
            <p class="mb-1"><small>Logged in as:</small></p>
            <p class="mb-2"><strong><?php echo htmlspecialchars(get_user_name()); ?></strong></p>
        </div>
        <a href="../auth/logout.php" class="btn btn-sm btn-danger w-100">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>
