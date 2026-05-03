<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kapelagi');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

function ensure_order_cancellation_reason_column($conn) {
    static $checked = false;
    if ($checked) {
        return;
    }

    $checkSql = "SHOW COLUMNS FROM orders LIKE 'cancellation_reason'";
    $result = $conn->query($checkSql);
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN cancellation_reason TEXT NULL AFTER special_notes");
    }

    $checked = true;
}

function ensure_order_archive_columns($conn) {
    static $checked = false;
    if ($checked) {
        return;
    }

    $isArchivedCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'is_archived'");
    if ($isArchivedCheck && $isArchivedCheck->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
    }

    $archivedAtCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'archived_at'");
    if ($archivedAtCheck && $archivedAtCheck->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN archived_at DATETIME NULL AFTER is_archived");
    }

    $checked = true;
}

?>
