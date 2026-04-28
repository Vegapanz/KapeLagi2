<?php
// Create Admin User Setup Script
// This file is used to create the initial admin user

include 'config/db.php';

// Admin credentials
$admin_email = 'admin@kapelagi.com';
$admin_name = 'Admin User';
$admin_password = 'admin123'; // Change this to a secure password!
$admin_password_hashed = password_hash($admin_password, PASSWORD_DEFAULT);

// Check if role column exists, if not, add it
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($result->num_rows == 0) {
    $alter_sql = "ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer' AFTER email_verified_at";
    if ($conn->query($alter_sql) === TRUE) {
        echo "✓ Role column added to users table<br>";
    } else {
        echo "✗ Error adding role column: " . $conn->error . "<br>";
    }
}

// Check if admin already exists
$check_sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "✓ Admin user already exists<br>";
    echo "<br><strong>Admin Credentials:</strong><br>";
    echo "Email: " . $admin_email . "<br>";
    echo "Password: " . $admin_password . "<br>";
} else {
    // Create admin user
    $now = date('Y-m-d H:i:s');
    $insert_sql = "INSERT INTO users (name, email, password, email_verified_at, role, created_at, updated_at) 
                   VALUES (?, ?, ?, ?, 'admin', ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ssssss", $admin_name, $admin_email, $admin_password_hashed, $now, $now, $now);
    
    if ($stmt->execute()) {
        echo "✓ Admin user created successfully!<br>";
        echo "<br><strong>Admin Credentials:</strong><br>";
        echo "Email: " . $admin_email . "<br>";
        echo "Password: " . $admin_password . "<br>";
        echo "<br><strong>IMPORTANT:</strong> Please change this password after your first login!<br>";
    } else {
        echo "✗ Error creating admin user: " . $conn->error . "<br>";
    }
}

$conn->close();
?>
