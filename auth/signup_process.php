<?php
include 'config/db.php';
include 'config/session.php';
include 'auth/email_verification.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $terms_accepted = isset($_POST['terms_accepted']) && $_POST['terms_accepted'] === '1';
    $terms_version = 'v1.0';

    ensure_user_verification_columns($conn);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = "Password must contain at least one number";
    } elseif (!preg_match('/[!@#$%^&*]/', $password)) {
        $error = "Password must contain at least one special character (!@#$%^&*)";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!$terms_accepted) {
        $error = "You must accept the Terms and Conditions to continue";
    } elseif (empty($_SESSION['signup_email_verified'])
        || empty($_SESSION['signup_verified_email'])
        || strcasecmp($_SESSION['signup_verified_email'], $email) !== 0) {
        $error = "Please verify this email first before creating your account.";
    } else {
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Email already registered";
        } else {
            // Email is already verified at this step via code entry before submission.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_sql = "INSERT INTO users (name, email, password, email_verified_at, terms_accepted_at, terms_version) VALUES (?, ?, ?, NOW(), NOW(), ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $terms_version);
            
            if ($insert_stmt->execute()) {
                $success = "Account created successfully. You can now sign in.";

                // Clear one-time signup verification session data after successful registration.
                unset($_SESSION['signup_email_verified']);
                unset($_SESSION['signup_verified_email']);
                unset($_SESSION['temp_verification_email']);
                unset($_SESSION['temp_verification_code']);
                unset($_SESSION['temp_verification_token']);
                unset($_SESSION['temp_verification_sent_at']);
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
