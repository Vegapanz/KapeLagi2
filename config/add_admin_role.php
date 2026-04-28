<?php
// Migration: Add admin role support
// Run this once to update the database schema

include 'db.php';

// Check if role column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");

if ($result->num_rows == 0) {
    $sql = "ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer' AFTER email_verified_at";
    
    if ($conn->query($sql) === TRUE) {
        echo "Role column added successfully<br>";
    } else {
        echo "Error adding role column: " . $conn->error . "<br>";
    }
} else {
    echo "Role column already exists<br>";
}

// Close connection
$conn->close();
?>
