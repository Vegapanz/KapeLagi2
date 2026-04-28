<?php
include 'config/db.php';
include 'config/session.php';
include 'auth/email_verification.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    ensure_user_verification_columns($conn);
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
    } else {
        // Check user
        $sql = "SELECT id, name, email, password, email_verified_at, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                if (empty($user['email_verified_at'])) {
                    $error = "Please verify your email before signing in.";
                } else {
                    $role = $user['role'] ?? 'customer';
                    login_user($user['id'], $user['name'], $user['email'], $role);
                    
                    // Redirect based on role
                    if ($role === 'admin') {
                        header('Location: admin/dashboard.php');
                    } else {
                        header('Location: index.php');
                    }
                    exit;
                }
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Email not found";
        }
    }
}
?>
