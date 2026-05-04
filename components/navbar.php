<?php
require_once __DIR__ . '/../config/session.php';

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// Helper function to check if link is active
function is_page_active($page) {
    global $current_page;
    return $current_page === $page || ($page === 'index.php' && $current_page === '');
}
?>
<nav class="navbar navbar-expand-lg navbar-light navbar-custom">
    <div class="container-fluid px-5">
        <!-- Logo -->
        <a class="navbar-brand" href="index.php">
            <span class="logo-text">KAPELAGI</span>
        </a>
        
        <!-- Toggle button for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link <?php echo is_page_active('menu.php') ? 'active' : ''; ?>" href="menu.php">MENU</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_page_active('about.php') ? 'active' : ''; ?>" href="about.php">ABOUT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_page_active('contact.php') ? 'active' : ''; ?>" href="contact.php">CONTACT</a>
                </li>
                
                <?php if (is_logged_in()): ?>
                    <!-- Shopping Cart Icon -->
                    <li class="nav-item ms-3">
                        <a class="nav-link cart-icon" href="checkout.php" title="Shopping Cart">
                            <i class="fas fa-shopping-cart"></i>
                            <span id="cartCountBadge" class="cart-badge" style="display:none;font-size:0.75rem;line-height:1;margin-left:6px;padding:2px 6px;border-radius:12px;background:#dc3545;color:#fff;vertical-align:top;">0</span>
                        </a>
                    </li>
                    
                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link user-profile" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" 
                           aria-expanded="false" title="User Profile">
                            <i class="fas fa-user-circle"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-3">
                        <a class="sign-in-btn" href="signin.php">Sign in</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const badge = document.getElementById('cartCountBadge');

        function updateBadge(count) {
            if (!badge) return;
            const n = parseInt(count, 10) || 0;
            if (n > 0) {
                badge.textContent = n;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }

        // Initial fetch to populate badge
        fetch('api/cart.php?action=get_cart')
            .then(r => r.json())
            .then(data => {
                if (data && data.success && Array.isArray(data.cart)) {
                    updateBadge(data.cart.length);
                } else {
                    updateBadge(0);
                }
            })
            .catch(() => updateBadge(0));

        // Listen for cart updates from cart sidebar
        document.addEventListener('cartUpdated', function(e) {
            const cnt = e && e.detail && typeof e.detail.count === 'number' ? e.detail.count : 0;
            updateBadge(cnt);
        });
    });
</script>
