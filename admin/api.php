<?php
// API endpoints for admin functionality
include '../config/session.php';
include '../config/db.php';

// Verify admin access
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    // MENU ITEMS MANAGEMENT
    case 'add-menu-item':
        add_menu_item($conn);
        break;
    
    case 'edit-menu-item':
        edit_menu_item($conn);
        break;
    
    case 'delete-menu-item':
        delete_menu_item($conn);
        break;

    case 'restore-menu-item':
        restore_menu_item($conn);
        break;

    case 'update-stock':
        update_stock($conn);
        break;
    
    // ORDERS MANAGEMENT
    case 'update-order-status':
        update_order_status($conn);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

// HANDLE IMAGE UPLOAD
function handleImageUpload($file) {
    $uploadDir = '../assets/Images/products/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file
    $maxSize = 5 * 1024 * 1024; // 5MB
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('product_') . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Return relative path from web root
        return 'assets/Images/products/' . $filename;
    }
    
    return false;
}

// ADD MENU ITEM
function add_menu_item($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price_16oz = floatval($_POST['price_16oz'] ?? 0);
    $price_22oz = floatval($_POST['price_22oz'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    if (empty($name) || empty($category) || $price_16oz <= 0 || $price_22oz <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }

    $image_url = '';
    if (!empty($_FILES['image']['name'])) {
        $image_url = handleImageUpload($_FILES['image']);
        if (!$image_url) {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to upload image']);
            exit;
        }
    }

    $sql = "INSERT INTO products (name, description, category, price_16oz, price_22oz, stock, image_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssddis", $name, $description, $category, $price_16oz, $price_22oz, $stock, $image_url);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Menu item added successfully',
            'id' => $stmt->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add menu item: ' . $conn->error]);
    }
}

// EDIT MENU ITEM
function edit_menu_item($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price_16oz = floatval($_POST['price_16oz'] ?? 0);
    $price_22oz = floatval($_POST['price_22oz'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    if ($id <= 0 || empty($name) || empty($category)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    if (!empty($_FILES['image']['name'])) {
        $image_url = handleImageUpload($_FILES['image']);
        if (!$image_url) {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to upload image']);
            exit;
        }
        $sql = "UPDATE products SET name = ?, description = ?, category = ?, price_16oz = ?, price_22oz = ?, stock = ?, image_url = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssddiis", $name, $description, $category, $price_16oz, $price_22oz, $stock, $image_url, $id);
    } else {
        $sql = "UPDATE products SET name = ?, description = ?, category = ?, price_16oz = ?, price_22oz = ?, stock = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssddii", $name, $description, $category, $price_16oz, $price_22oz, $stock, $id);
    }

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Menu item updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update menu item']);
    }
}

// DELETE MENU ITEM
function delete_menu_item($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid ID']);
        exit;
    }

    // Check if product exists
    $check = $conn->query("SELECT id FROM products WHERE id = $id");
    if ($check->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    ensure_product_archive_columns($conn);

    $sql = "UPDATE products SET is_archived = 1, archived_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Menu item archived successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to archive menu item']);
    }
}

// RESTORE MENU ITEM
function restore_menu_item($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid ID']);
        exit;
    }

    ensure_product_archive_columns($conn);

    $sql = "UPDATE products SET is_archived = 0, archived_at = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Menu item restored successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to restore menu item']);
    }
}

// UPDATE STOCK
function update_stock($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    $id = intval($_POST['id'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    if ($id <= 0 || $stock < 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    // Check if product exists
    $check = $conn->query("SELECT id FROM products WHERE id = $id");
    if ($check->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    // Update stock
    $sql = "UPDATE products SET stock = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $stock, $id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Stock updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update stock']);
    }
}

// UPDATE ORDER STATUS
function update_order_status($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    ensure_order_cancellation_reason_column($conn);

    $order_id = intval($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $cancellation_note = trim($_POST['cancellation_note'] ?? '');
    $valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];

    if ($order_id <= 0 || !in_array($status, $valid_statuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $note_value = null;
    if ($status === 'cancelled') {
        if ($cancellation_note === '') {
            $noteSql = "SELECT cancellation_reason FROM orders WHERE id = ?";
            $noteStmt = $conn->prepare($noteSql);
            $noteStmt->bind_param("i", $order_id);
            $noteStmt->execute();
            $noteResult = $noteStmt->get_result();
            $existing = $noteResult ? $noteResult->fetch_assoc() : null;
            $note_value = $existing['cancellation_reason'] ?? null;
        } else {
            $note_value = $cancellation_note;
        }
    }

    if ($status === 'cancelled') {
        $sql = "UPDATE orders SET status = ?, cancellation_reason = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $note_value, $order_id);
    } else {
        $sql = "UPDATE orders SET status = ?, cancellation_reason = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $order_id);
    }

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update order status']);
    }
}
?>
