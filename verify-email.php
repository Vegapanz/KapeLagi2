<?php
include 'config/db.php';
include 'auth/email_verification.php';

ensure_user_verification_columns($conn);

$status = 'error';
$message = 'Invalid verification link.';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if ($token !== '') {
    // Check for pending email verification first
    $pending_sql = "SELECT id, pending_email FROM users WHERE pending_email_verification_token = ? LIMIT 1";
    $pending_stmt = $conn->prepare($pending_sql);
    $pending_stmt->bind_param('s', $token);
    $pending_stmt->execute();
    $pending_result = $pending_stmt->get_result();

    if ($pending_result->num_rows === 1) {
        $user = $pending_result->fetch_assoc();
        $new_email = $user['pending_email'];

        $updateSql = "UPDATE users SET email = ?, email_verified_at = NOW(), pending_email = NULL, pending_email_verification_token = NULL WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('si', $new_email, $user['id']);

        if ($updateStmt->execute()) {
            $status = 'success';
            $message = 'Email updated and verified successfully. Your new email is now active.';
        } else {
            $message = 'Unable to verify email right now. Please try again.';
        }
    } else {
        // Check for initial sign-up verification
        $signup_sql = "SELECT id, email_verified_at FROM users WHERE email_verification_token = ? LIMIT 1";
        $signup_stmt = $conn->prepare($signup_sql);
        $signup_stmt->bind_param('s', $token);
        $signup_stmt->execute();
        $signup_result = $signup_stmt->get_result();

        if ($signup_result->num_rows === 1) {
            $user = $signup_result->fetch_assoc();

            if (!empty($user['email_verified_at'])) {
                $status = 'info';
                $message = 'Your email is already verified. You can sign in now.';
            } else {
                $updateSql = "UPDATE users SET email_verified_at = NOW(), email_verification_token = NULL WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param('i', $user['id']);

                if ($updateStmt->execute()) {
                    $status = 'success';
                    $message = 'Email verified successfully. You can now sign in.';
                } else {
                    $message = 'Unable to verify email right now. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body style="background:#E8E0D0; min-height:100vh;">
    <?php include 'components/navbar.php'; ?>

    <main class="container py-5" style="max-width: 760px;">
        <div class="card border-0 shadow-sm" style="border-radius:20px; background:#f7f2e6;">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 mb-3" style="font-family: var(--font-header);">Email Verification</h1>

                <?php if ($status === 'success'): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php elseif ($status === 'info'): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                <?php else: ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <a href="signin.php" class="btn btn-dark">Go to Sign In</a>
            </div>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
