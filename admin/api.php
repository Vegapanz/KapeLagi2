<?php
// API endpoints for admin functionality
include '../config/session.php';
include '../config/db.php';
include '../config/paymongo.php';

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

    // INGREDIENTS / INVENTORY
    case 'get-ingredients':
        get_ingredients($conn);
        break;

    case 'add-ingredient':
        add_ingredient($conn);
        break;

    case 'update-ingredient':
        update_ingredient($conn);
        break;

    case 'update-ingredient-stock':
        update_ingredient_stock($conn);
        break;

    case 'delete-ingredient':
        delete_ingredient($conn);
        break;

    // PRODUCT-INGREDIENT MAPPINGS
    case 'get-product-ingredients':
        get_product_ingredients($conn);
        break;

    case 'add-product-ingredient':
        add_product_ingredient($conn);
        break;

    case 'save-product-ingredients':
        save_product_ingredients($conn);
        break;

    case 'update-product-ingredient':
        update_product_ingredient($conn);
        break;

    case 'update-product-ingredients':
        save_product_ingredients($conn);
        break;

    case 'delete-product-ingredient':
        delete_product_ingredient($conn);
        break;
    
    // ORDERS MANAGEMENT
    case 'update-order-status':
        update_order_status($conn);
        break;

    case 'refresh-gcash-status':
        refresh_gcash_status($conn);
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

// ---------------------
// INGREDIENTS / INVENTORY
// ---------------------
function get_ingredients($conn) {
    $sql = "SELECT i.id, i.name, i.unit, i.stock, i.package_size, i.package_unit, i.density_g_per_ml, i.low_stock_threshold, i.updated_at, COALESCE(i.category,'other') AS category,
                GROUP_CONCAT(DISTINCT p.category SEPARATOR ',') AS product_categories
            FROM ingredients i
            LEFT JOIN product_ingredients pi ON pi.ingredient_id = i.id
            LEFT JOIN products p ON p.id = pi.product_id
            GROUP BY i.id
            ORDER BY i.name ASC";
    $res = $conn->query($sql);
    $out = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $row['product_categories'] = $row['product_categories'] ?? '';
            $out[] = $row;
        }
    }
    echo json_encode(['success' => true, 'ingredients' => $out]);
}

function add_ingredient($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid method']);
        exit;
    }
    $name = trim($_POST['name'] ?? '');
    $unit = trim($_POST['unit'] ?? 'units');
    $stock = floatval($_POST['stock'] ?? 0);
    $package_size = floatval($_POST['package_size'] ?? 1);
    $package_unit = trim($_POST['package_unit'] ?? 'pieces');
    $density_g_per_ml = floatval($_POST['density_g_per_ml'] ?? 1);
    $threshold = floatval($_POST['low_stock_threshold'] ?? 5);

    if ($name === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Name required']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO ingredients (name, unit, stock, package_size, package_unit, density_g_per_ml, low_stock_threshold) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssddsdd', $name, $unit, $stock, $package_size, $package_unit, $density_g_per_ml, $threshold);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add ingredient']);
    }
}

function update_ingredient($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid method']);
        exit;
    }
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $unit = trim($_POST['unit'] ?? 'units');
    $stock = floatval($_POST['stock'] ?? 0);
    $package_size = floatval($_POST['package_size'] ?? 1);
    $package_unit = trim($_POST['package_unit'] ?? 'pieces');
    $density_g_per_ml = floatval($_POST['density_g_per_ml'] ?? 1);
    $threshold = floatval($_POST['low_stock_threshold'] ?? 5);

    if ($id <= 0 || $name === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE ingredients SET name = ?, unit = ?, stock = ?, package_size = ?, package_unit = ?, density_g_per_ml = ?, low_stock_threshold = ? WHERE id = ?");
    $stmt->bind_param('ssddsddi', $name, $unit, $stock, $package_size, $package_unit, $density_g_per_ml, $threshold, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update ingredient']);
    }
}

function update_ingredient_stock($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid method']);
        exit;
    }
    $id = intval($_POST['id'] ?? 0);
    $stock = floatval($_POST['stock'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid id']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE ingredients SET stock = ? WHERE id = ?");
    $stmt->bind_param('di', $stock, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update stock']);
    }
}

function delete_ingredient($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid method']);
        exit;
    }
    $ids = [];
    if (isset($_POST['ids'])) {
        if (is_array($_POST['ids'])) {
            $ids = array_map('intval', $_POST['ids']);
        } else {
            $ids = array_map('intval', preg_split('/\s*,\s*/', (string) $_POST['ids'], -1, PREG_SPLIT_NO_EMPTY));
        }
    } else {
        $singleId = intval($_POST['id'] ?? 0);
        if ($singleId > 0) {
            $ids = [$singleId];
        }
    }

    $ids = array_values(array_filter(array_unique($ids), function ($value) {
        return $value > 0;
    }));

    if (empty($ids)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid id']);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $sql = "DELETE FROM ingredients WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare delete']);
        exit;
    }

    $params = [];
    $params[] = $types;
    foreach ($ids as $index => $id) {
        $params[] = &$ids[$index];
    }
    call_user_func_array([$stmt, 'bind_param'], $params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'deleted_count' => $stmt->affected_rows]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete ingredient']);
    }
}

// ---------------------
// PRODUCT - INGREDIENT MAPPINGS
// ---------------------
function get_product_ingredients($conn) {
    $sql = "SELECT pi.id, pi.product_id, pi.ingredient_id, pi.size, pi.stock_unit, pi.quantity_per_unit, pi.unit, p.name AS product_name, i.name AS ingredient_name
            FROM product_ingredients pi
            LEFT JOIN products p ON p.id = pi.product_id
            LEFT JOIN ingredients i ON i.id = pi.ingredient_id
            ORDER BY p.name, pi.size, i.name";
    $res = $conn->query($sql);
    $out = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $out[] = $r;
    }
    echo json_encode(['success' => true, 'mappings' => $out]);
}

function add_product_ingredient($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(400); echo json_encode(['error'=>'Invalid method']); exit; }
    $product_id = intval($_POST['product_id'] ?? 0);
    $ingredient_id = intval($_POST['ingredient_id'] ?? 0);
    $size = trim($_POST['size'] ?? '16oz');
    $stock_unit = trim($_POST['stock_unit'] ?? 'unit');
    $qty = floatval($_POST['quantity_per_unit'] ?? 0);
    $unit = trim($_POST['unit'] ?? 'units');
    if ($product_id<=0 || $ingredient_id<=0 || $qty<=0) { http_response_code(400); echo json_encode(['error'=>'Invalid input']); exit; }
    $stmt = $conn->prepare("INSERT INTO product_ingredients (product_id, ingredient_id, size, stock_unit, quantity_per_unit, unit) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iissds', $product_id, $ingredient_id, $size, $stock_unit, $qty, $unit);
    if ($stmt->execute()) echo json_encode(['success'=>true,'id'=>$stmt->insert_id]); else { http_response_code(500); echo json_encode(['error'=>'Failed']); }
}

// Save multiple ingredients for a product (replace existing mappings)
function save_product_ingredients($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid method']);
        exit;
    }

    $product_id = intval($_POST['product_id'] ?? 0);
    $size = trim($_POST['size'] ?? '16oz');
    $payload = $_POST['payload'] ?? '';
    if ($product_id <= 0 || $payload === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $items = json_decode($payload, true);
    if (!is_array($items) || count($items) === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }
    if (count($items) > 7) {
        http_response_code(400);
        echo json_encode(['error' => 'Max 7 ingredients allowed']);
        exit;
    }

    $conn->begin_transaction();
    try {
        $del = $conn->prepare("DELETE FROM product_ingredients WHERE product_id = ? AND size = ?");
        $del->bind_param('is', $product_id, $size);
        $del->execute();
        $del->close();

        $ins = $conn->prepare("INSERT INTO product_ingredients (product_id, ingredient_id, size, stock_unit, quantity_per_unit, unit) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$ins) throw new Exception('Prepare failed: ' . $conn->error);

        foreach ($items as $it) {
            $ing = intval($it['ingredient_id'] ?? 0);
            $qty = floatval($it['quantity_per_unit'] ?? 0);
            $stock_unit = trim($it['stock_unit'] ?? 'unit');
            $unit = trim($it['unit'] ?? 'units');
            if ($ing <= 0 || $qty <= 0) throw new Exception('Invalid item in payload');
            // types: product_id(i), ingredient_id(i), size(s), stock_unit(s), quantity_per_unit(d), unit(s)
            $ins->bind_param('iissds', $product_id, $ing, $size, $stock_unit, $qty, $unit);
            if (!$ins->execute()) throw new Exception('Insert failed: ' . $ins->error);
        }
        $ins->close();

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save mappings: ' . $e->getMessage()]);
    }
}



function update_product_ingredient($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(400); echo json_encode(['error'=>'Invalid method']); exit; }
    $id = intval($_POST['id'] ?? 0);
    $size = trim($_POST['size'] ?? '16oz');
    $stock_unit = trim($_POST['stock_unit'] ?? 'unit');
    $qty = floatval($_POST['quantity_per_unit'] ?? 0);
    $unit = trim($_POST['unit'] ?? 'units');
    if ($id<=0 || $qty<=0) { http_response_code(400); echo json_encode(['error'=>'Invalid input']); exit; }
    $stmt = $conn->prepare("UPDATE product_ingredients SET size = ?, stock_unit = ?, quantity_per_unit = ?, unit = ? WHERE id = ?");
    $stmt->bind_param('ssdsi', $size, $stock_unit, $qty, $unit, $id);
    if ($stmt->execute()) echo json_encode(['success'=>true]); else { http_response_code(500); echo json_encode(['error'=>'Failed']); }
}

function delete_product_ingredient($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(400); echo json_encode(['error'=>'Invalid method']); exit; }
    $id = intval($_POST['id'] ?? 0);
    if ($id<=0) { http_response_code(400); echo json_encode(['error'=>'Invalid id']); exit; }
    $stmt = $conn->prepare("DELETE FROM product_ingredients WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) echo json_encode(['success'=>true]); else { http_response_code(500); echo json_encode(['error'=>'Failed']); }
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

// Refresh PayMongo GCash source status for an order
function refresh_gcash_status($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    $order_id = intval($_POST['order_id'] ?? 0);
    if ($order_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid order ID']);
        exit;
    }

    $stmt = $conn->prepare("SELECT payment_method, payment_intent_id, status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result ? $result->fetch_assoc() : null;

    if (!$order || $order['payment_method'] !== 'GCASH' || empty($order['payment_intent_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Order not eligible for GCash refresh']);
        exit;
    }

    $source = getPaymongoSource($order['payment_intent_id']);
    if (!$source || !isset($source['data']['attributes']['status'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to retrieve PayMongo source status']);
        exit;
    }

    $source_status = $source['data']['attributes']['status'];
    $new_status = $order['status'];
    if (in_array($source_status, ['paid', 'authorized', 'chargeable'], true)) {
        $new_status = 'processing';
    } elseif (in_array($source_status, ['failed', 'cancelled'], true)) {
        $new_status = 'cancelled';
    } elseif ($source_status === 'pending') {
        $new_status = 'pending';
    }

    if ($new_status !== $order['status']) {
        $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $order_id);
        $update_stmt->execute();
    }

    echo json_encode([
        'success' => true,
        'source_status' => $source_status,
        'order_status' => $new_status
    ]);
}
?>
