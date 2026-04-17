<?php
header('Content-Type: application/json');
include 'config/db.php';
include 'config/session.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Check if user is logged in
if (!is_logged_in() && $action !== 'get_cart') {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = get_user_id();

if ($action == 'add_to_cart') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $size = isset($_POST['size']) ? $_POST['size'] : '16oz';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $special_instructions = isset($_POST['special_instructions']) ? trim($_POST['special_instructions']) : '';
    
    if ($product_id > 0) {
        // Check if item already in cart
        $check_sql = "SELECT id FROM cart WHERE user_id = ? AND product_id = ? AND size = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("iis", $user_id, $product_id, $size);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update quantity
            $update_sql = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ? AND size = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("iis", $quantity, $user_id, $product_id, $size);
            $update_stmt->execute();
        } else {
            // Add new item
            $insert_sql = "INSERT INTO cart (user_id, product_id, size, quantity, special_instructions) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iisss", $user_id, $product_id, $size, $quantity, $special_instructions);
            $insert_stmt->execute();
        }
        
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    }
}

elseif ($action == 'get_cart') {
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    
    $sql = "SELECT c.id, p.id as product_id, p.name, p.category, p.image_url, c.size, c.quantity, p.price_16oz, p.price_22oz, c.special_instructions 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? 
            ORDER BY c.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cart_items = [];
    $subtotal = 0;
    
    while ($row = $result->fetch_assoc()) {
        $price = $row['size'] == '22oz' ? $row['price_22oz'] : $row['price_16oz'];
        $item_total = $price * $row['quantity'];
        $subtotal += $item_total;
        
        $row['price'] = $price;
        $row['total_price'] = $item_total;
        $cart_items[] = $row;
    }
    
    $shipping = 0; // Free shipping for now
    $total = $subtotal + $shipping;
    
    echo json_encode([
        'success' => true,
        'cart' => $cart_items,
        'totals' => [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total
        ]
    ]);
}

elseif ($action == 'remove_from_cart') {
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    
    if ($cart_id > 0) {
        $delete_sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $cart_id, $user_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Removed from cart']);
        }
    }
}

elseif ($action == 'update_cart') {
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($cart_id > 0 && $quantity > 0) {
        $update_sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iii", $quantity, $cart_id, $user_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
        }
    }
}

elseif ($action == 'create_order') {
    $customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
    $customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
    $customer_phone = isset($_POST['customer_phone']) ? trim($_POST['customer_phone']) : '';
    $delivery_address = isset($_POST['delivery_address']) ? trim($_POST['delivery_address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $province = isset($_POST['province']) ? trim($_POST['province']) : '';
    
    // Get cart items
    $cart_sql = "SELECT c.id, p.id as product_id, p.name, c.size, c.quantity, p.price_16oz, p.price_22oz 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.user_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    
    if ($cart_result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }
    
    // Calculate total
    $total_amount = 0;
    $cart_items = [];
    while ($item = $cart_result->fetch_assoc()) {
        $price = $item['size'] == '22oz' ? $item['price_22oz'] : $item['price_16oz'];
        $total_amount += $price * $item['quantity'];
        $cart_items[] = $item;
    }
    
    // Create order
    $order_sql = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, delivery_address, city, province, total_amount) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("issssssd", $user_id, $customer_name, $customer_email, $customer_phone, $delivery_address, $city, $province, $total_amount);
    
    if ($order_stmt->execute()) {
        $order_id = $conn->insert_id;
        
        // Add order items
        $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, size, price, quantity) VALUES (?, ?, ?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);
        
        foreach ($cart_items as $item) {
            $price = $item['size'] == '22oz' ? $item['price_22oz'] : $item['price_16oz'];
            $item_stmt->bind_param("iissdi", $order_id, $item['product_id'], $item['name'], $item['size'], $price, $item['quantity']);
            $item_stmt->execute();
        }
        
        // Clear cart
        $clear_sql = "DELETE FROM cart WHERE user_id = ?";
        $clear_stmt = $conn->prepare($clear_sql);
        $clear_stmt->bind_param("i", $user_id);
        $clear_stmt->execute();
        
        echo json_encode(['success' => true, 'order_id' => $order_id, 'message' => 'Order created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create order']);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
