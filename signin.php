<?php
include 'config/session.php';

// Check if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'auth/signin_process.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body >
    <!-- Navigation Bar -->
    <?php include 'components/navbar.php'; ?>
    <div class="auth-page">
    <!-- Coffee Bean Decorations -->
    <div class="coffee-bean bean-1"></div>
    <div class="coffee-bean bean-2"></div>
    <div class="coffee-bean bean-3"></div>
    <div class="coffee-bean bean-4"></div>
    <div class="coffee-bean bean-5"></div>
    <div class="coffee-bean bean-6"></div>
    <div class="coffee-bean bean-7"></div>
    
    <!-- Auth Container -->
    <div class="auth-container">
        <div class="auth-card">
            <!-- Form Section -->
            <div class="auth-form-section">
                <h2 class="auth-title">Sign in</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form class="auth-form" method="POST" action="signin.php">
                    <label class="auth-label">Email:</label>
                    <input type="email" name="email" class="auth-input" placeholder="Your Email" required>
                    
                    <label class="auth-label">Password:</label>
                    <input type="password" name="password" class="auth-input" placeholder="Password" required>
                    
                    <a href="#" class="forgot-password">Forgot password?</a>
                    
                    <button type="submit" class="auth-btn primary-btn">Sign in</button>
                </form>
                
                <div class="auth-divider">
                    <span>OR</span>
                </div>
                
                <button class="auth-btn google-btn">
                    <span class="google-icon">G</span>
                    Continue with Google
                </button>
                
                <p class="auth-link-text">
                    Don't have account? <a href="signup.php" class="auth-link">Sign up</a>
                </p>
            </div>
            
            <!-- Image Section -->
            <div class="auth-character-section auth-image-section">
                <img src="assets/Images/coffee4.jpg" alt="Freshly brewed coffee" class="auth-side-image">
            </div>
        </div>
    </div>
    </div>
    
    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="assets/js/script.js"></script>
</body>
</html>
