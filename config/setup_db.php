<?php
// Database Schema Setup File
// Run this once to create all tables

include 'db.php';

// SQL statements to create tables
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    city VARCHAR(50),
    province VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$sql_products = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    image_url VARCHAR(255),
    price_16oz DECIMAL(10, 2),
    price_22oz DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql_cart = "CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    size VARCHAR(10),
    quantity INT DEFAULT 1,
    special_instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

$sql_orders = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    delivery_address VARCHAR(255),
    city VARCHAR(50),
    province VARCHAR(50),
    total_amount DECIMAL(10, 2),
    status VARCHAR(20) DEFAULT 'pending',
    special_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

$sql_order_items = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(100),
    size VARCHAR(10),
    price DECIMAL(10, 2),
    quantity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
)";

// Execute the SQL statements
if ($conn->query($sql_users) === TRUE) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

if ($conn->query($sql_products) === TRUE) {
    echo "Products table created successfully<br>";
} else {
    echo "Error creating products table: " . $conn->error . "<br>";
}

if ($conn->query($sql_cart) === TRUE) {
    echo "Cart table created successfully<br>";
} else {
    echo "Error creating cart table: " . $conn->error . "<br>";
}

if ($conn->query($sql_orders) === TRUE) {
    echo "Orders table created successfully<br>";
} else {
    echo "Error creating orders table: " . $conn->error . "<br>";
}

if ($conn->query($sql_order_items) === TRUE) {
    echo "Order items table created successfully<br>";
} else {
    echo "Error creating order items table: " . $conn->error . "<br>";
}

// Insert sample products
$sample_products = [
    ["Spanish Latte", "A sweet, creamy espresso-based drink made by combining espresso with both regular milk and sweetened condensed milk", "Coffee", 120, 120],
    ["Americano", "A classic espresso stretched with hot water for a smooth, full-bodied flavor", "Coffee", 100, 100],
    ["Blueberry Milk", "Smooth blueberry flavor combined with creamy milk", "Non-Coffee", 110, 110],
    ["Matcha Latte", "Vibrant green tea powder whisked with hot milk for a refreshing drink", "Coffee", 130, 130],
    ["Caramel Macchiato", "Rich caramel sauce layered with espresso and velvety steamed milk", "Coffee", 120, 120],
    ["Vanilla Latte", "Smooth vanilla flavor combined with espresso and creamy milk", "Coffee", 110, 110]
];

foreach ($sample_products as $product) {
    $check_sql = "SELECT id FROM products WHERE name = '" . $conn->real_escape_string($product[0]) . "'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows == 0) {
        $insert_sql = "INSERT INTO products (name, description, category, price_16oz, price_22oz) 
                       VALUES ('" . $conn->real_escape_string($product[0]) . "', 
                               '" . $conn->real_escape_string($product[1]) . "', 
                               '" . $conn->real_escape_string($product[2]) . "', 
                               " . $product[3] . ", 
                               " . $product[4] . ")";
        $conn->query($insert_sql);
    }
}

echo "Database setup complete!<br>";
echo "<a href='../index.php'>Back to home</a>";

$conn->close();
?>
