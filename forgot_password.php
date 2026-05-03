<?php
include 'config/session.php';

$error = '';
$message = '';
if (isset($_GET['sent'])) {
    $message = 'A password reset link has been sent to the email address on file.';
}
if (isset($_GET['error']) && trim($_GET['error']) !== '') {
    $error = trim($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-form-section">
                    <h2 class="auth-title">Forgot your password?</h2>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <form class="auth-form" method="POST" action="auth/forgot_password_process.php">
                        <label class="auth-label">Enter your account email:</label>
                        <input type="email" name="email" class="auth-input" placeholder="Email" required>
                        <button type="submit" class="auth-btn primary-btn">Send reset link</button>
                    </form>
                    <p class="auth-link-text">Remembered? <a href="signin.php">Sign in</a></p>
                </div>
                <div class="auth-character-section auth-image-section">
                    <img src="assets/Images/coffee4.jpg" alt="coffee" class="auth-side-image">
                </div>
            </div>
        </div>
    </div>
    <?php include 'components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
