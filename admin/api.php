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
    
    // ORDERS MANAGEMENT
    case 'update-order-status':
        update_order_status($conn);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
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

    if (empty($name) || empty($category) || $price_16oz <= 0 || $price_22oz <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }

    $sql = "INSERT INTO products (name, description, category, price_16oz, price_22oz) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdd", $name, $description, $category, $price_16oz, $price_22oz);

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

    if ($id <= 0 || empty($name) || empty($category)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $sql = "UPDATE products SET name = ?, description = ?, category = ?, price_16oz = ?, price_22oz = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssddi", $name, $description, $category, $price_16oz, $price_22oz, $id);

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

    // Delete product
    $sql = "DELETE FROM products WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Menu item deleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete menu item']);
    }
}

// UPDATE ORDER STATUS
function update_order_status($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    $order_id = intval($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];

    if ($order_id <= 0 || !in_array($status, $valid_statuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);

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
