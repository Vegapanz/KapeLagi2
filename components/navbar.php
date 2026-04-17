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
